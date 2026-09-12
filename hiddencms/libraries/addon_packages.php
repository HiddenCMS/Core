<?php

namespace HB\HiddenCMS\Libraries;

use Composer\InstalledVersions;
use Composer\Semver\Semver;
use HB\HiddenCMS\Addons\Migration;
use HB\HiddenCMS\Addons\Seeder;
use HB\HiddenCMS\Library;
use RuntimeException;

class Addon_Packages extends Library
{
	const PACKAGE_TYPE = 'hiddencms-addon';

	protected $types = ['module', 'widget', 'theme', 'language', 'authenticator'];

	public function discover()
	{
		$packages = [];

		if (!class_exists(InstalledVersions::class))
		{
			return $packages;
		}

		foreach (InstalledVersions::getInstalledPackages() as $package)
		{
			$path = InstalledVersions::getInstallPath($package);

			if (!$path || !is_file($manifest_file = $path.'/composer.json'))
			{
				continue;
			}

			$composer = json_decode(file_get_contents($manifest_file), TRUE);
			$package_type = isset($composer['type']) ? $composer['type'] : 'library';
			$info = isset($composer['extra']['hiddencms']) && is_array($composer['extra']['hiddencms']) ? $composer['extra']['hiddencms'] : [];

			if ($package_type !== self::PACKAGE_TYPE && strpos($package_type, self::PACKAGE_TYPE.'-') !== 0)
			{
				continue;
			}

			$definitions = isset($info['addons']) && is_array($info['addons']) ? $info['addons'] : [$info];
			$addons = [];

			foreach ($definitions as $definition)
			{
				if (!is_array($definition))
				{
					continue;
				}

				$type = isset($definition['type']) ? strtolower($definition['type']) : substr($package_type, strlen(self::PACKAGE_TYPE) + 1);
				$name = isset($definition['name']) ? $definition['name'] : preg_replace('/^.*\//', '', $package);
				$name = strtolower(str_replace('-', '_', $name));

				if (!in_array($type, $this->types, TRUE) || !preg_match('/^[a-z][a-z0-9_]*$/', $name))
				{
					continue;
				}

				$addons[] = [
					'type'  => $type,
					'name'  => $name,
					'class' => isset($definition['class']) ? $definition['class'] : NULL,
					'path'  => isset($definition['path']) ? trim(str_replace('\\', '/', $definition['path']), '/') : ''
				];
			}

			if (!$addons)
			{
				continue;
			}

			$packages[$package] = [
				'package'    => $package,
				'path'       => str_replace('\\', '/', $path),
				'version'    => InstalledVersions::getPrettyVersion($package) ?: InstalledVersions::getVersion($package),
				'constraint' => isset($composer['require']['hiddencms/core']) ? $composer['require']['hiddencms/core'] : NULL,
				'compatible' => $this->is_core_compatible(isset($composer['require']['hiddencms/core']) ? $composer['require']['hiddencms/core'] : NULL),
				'addons'     => $addons,
				'migrations' => $this->class_list(isset($info['migrations']) ? $info['migrations'] : []),
				'seeders'    => $this->class_list(isset($info['seeders']) ? $info['seeders'] : [])
			];
		}

		ksort($packages);

		return $packages;
	}

	public function sync($migrate = TRUE)
	{
		$results = [];
		$types = HB()->db->select('name', 'id')->from('addon_type')->index();

		if ($migrate)
		{
			$this->ensure_migrations_table();
		}

		foreach ($this->discover() as $package)
		{
			$applied = $migrate ? $this->apply_package($package) : [];
			$registered = [];

			foreach ($package['addons'] as $definition)
			{
				if (!isset($types[$definition['type']]))
				{
					throw new RuntimeException('Type d\'addon inconnu : '.$definition['type']);
				}

				$addon = HB()->collection('addon')
							  ->where('name', $definition['name'])
							  ->where('type_id', $types[$definition['type']])
							  ->row();

				$data = $addon && $addon() ? $addon->data : $this->array([
					'enabled' => FALSE
				]);

				$data->set('composer', [
					'package' => $package['package'],
					'version' => $package['version'],
					'class'   => $definition['class'],
					'path'    => $definition['path']
				]);

				if ($addon && $addon())
				{
					$addon->set('data', $data)->update();
				}
				else
				{
					$addon = HB()->model2('addon')
								  ->set('name', $definition['name'])
								  ->set('type', $types[$definition['type']])
								  ->set('data', $data)
								  ->create();
				}

				$registered[] = $addon;
			}

			$results[$package['package']] = [
				'addons'  => $registered,
				'applied' => $applied
			];
		}

		return $results;
	}

	public function require_package($package)
	{
		$this->validate_package($package, TRUE);

		return $this->run_composer(['require', $package]);
	}

	public function update_package($package)
	{
		$package = $this->validate_package($package);

		if (strpos($package, 'hiddencms/') === 0 && $this->development_constraint($this->package_constraint($package)))
		{
			$release = $this->stable_release($package);
			if (!$release)
			{
				throw new RuntimeException('Aucune version stable compatible disponible pour '.$package.'.');
			}
			return $this->run_composer(['require', $package.':^'.ltrim($release, 'v'), '--with-all-dependencies', '--prefer-stable']);
		}

		return $this->run_composer(['update', $package, '--with-dependencies', '--prefer-stable']);
	}

	public function install_dependencies()
	{
		return $this->run_composer(['install', '--prefer-dist', '--optimize-autoloader']);
	}

	public function update_dependencies(array $packages)
	{
		$packages = array_values(array_unique(array_filter($packages, function($package){
			return is_string($package) && preg_match('#^hiddencms/[a-z0-9_.-]+$#i', $package) && strtolower($package) !== 'hiddencms/core';
		})));

		if (!$packages)
		{
			return $this->install_dependencies();
		}

		return $this->run_composer(array_merge(
			['update'],
			$packages,
			['--with-all-dependencies', '--prefer-dist', '--optimize-autoloader']
		));
	}

	public function outdated()
	{
		$output = $this->run_composer(['outdated', '--all', '--direct', '--format=json'], FALSE);
		$start = strpos($output, '{');
		$end = strrpos($output, '}');

		if ($start === FALSE || $end === FALSE || !is_array($data = json_decode(substr($output, $start, $end - $start + 1), TRUE)))
		{
			throw new RuntimeException('Composer n\'a pas retourné un diagnostic de mises à jour valide.');
		}

		$result = [];

		foreach (isset($data['installed']) && is_array($data['installed']) ? $data['installed'] : [] as $package)
		{
			if (!empty($package['name']) && strpos($package['name'], 'hiddencms/') === 0 && $package['name'] !== 'hiddencms/core')
			{
				$constraint = $this->package_constraint($package['name']);
				$latest = $this->stable_release($package['name'], $this->development_constraint($constraint) ? NULL : $constraint);
				$current = isset($package['version']) ? $package['version'] : '';
				if (!$latest || (!$this->development_constraint($current) && version_compare(ltrim($latest, 'v'), ltrim($current, 'v'), '<=')))
				{
					continue;
				}
				$result[$package['name']] = [
					'package' => $package['name'],
					'current' => isset($package['version']) ? $package['version'] : NULL,
					'latest'  => $latest,
					'status'  => 'update-possible',
					'description' => isset($package['description']) ? $package['description'] : ''
				];
			}
		}

		return $result;
	}

	protected function package_constraint($package)
	{
		$manifest = json_decode(file_get_contents(HIDDENCMS_CMS.'/composer.json'), TRUE);
		return isset($manifest['require'][$package]) ? $manifest['require'][$package] : NULL;
	}

	protected function development_constraint($constraint)
	{
		return is_string($constraint) && preg_match('/(?:^|[\s|])dev-|(?:^|[\s|])[^\s|]+-dev(?:$|[\s|])/', $constraint);
	}

	protected function stable_release($package, $constraint = NULL)
	{
		$data = $this->composer_json(['show', $package, '--all', '--format=json']);
		$versions = array_filter(isset($data['versions']) ? $data['versions'] : [], function($version) use ($constraint){
			return preg_match('/^v?\d+\.\d+\.\d+$/', $version) && (!$constraint || Semver::satisfies($version, $constraint));
		});
		usort($versions, function($a, $b){ return version_compare(ltrim($b, 'v'), ltrim($a, 'v')); });
		foreach ($versions as $version)
		{
			$release = $this->composer_json(['show', $package, $version, '--all', '--format=json']);
			if ($this->is_core_compatible(isset($release['requires']['hiddencms/core']) ? $release['requires']['hiddencms/core'] : NULL))
			{
				return $version;
			}
		}
		return NULL;
	}

	protected function composer_json(array $arguments)
	{
		$output = $this->run_composer($arguments, FALSE);
		$start = strpos($output, '{');
		$end = strrpos($output, '}');
		$data = $start !== FALSE && $end !== FALSE ? json_decode(substr($output, $start, $end - $start + 1), TRUE) : NULL;
		if (!is_array($data))
		{
			throw new RuntimeException('Diagnostic Composer invalide.');
		}
		return $data;
	}

	public function compatibility()
	{
		$result = [];

		foreach ($this->discover() as $package => $info)
		{
			$result[$package] = [
				'constraint' => $info['constraint'],
				'compatible' => $info['compatible']
			];
		}

		return $result;
	}

	public function remove_package($package, $purge = FALSE)
	{
		$package = $this->validate_package($package);
		$installed = $this->discover();
		$rollbacks = $purge && isset($installed[$package]) ? $this->prepare_rollbacks($installed[$package]) : [];

		$output = $this->run_composer(['remove', $package]);

		if ($purge && isset($installed[$package]))
		{
			$this->rollback_package($installed[$package], $rollbacks);
		}

		if (isset($installed[$package]))
		{
			$types = HB()->db->select('name', 'id')->from('addon_type')->index();

			foreach ($installed[$package]['addons'] as $addon)
			{
				if (isset($types[$addon['type']]))
				{
					HB()->db->where('name', $addon['name'])
							 ->where('type_id', $types[$addon['type']])
							 ->delete('addon');
				}
			}
		}

		return $output;
	}

	protected function run_composer(array $arguments, $no_progress = TRUE)
	{
		if (!function_exists('proc_open'))
		{
			throw new RuntimeException('proc_open est requis pour lancer Composer depuis l\'administration.');
		}

		$command = array_merge($this->composer_command(), $arguments, ['--no-interaction']);

		if ($no_progress)
		{
			$command[] = '--no-progress';
		}
		$process = proc_open($command, [
			0 => ['pipe', 'r'],
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w']
		], $pipes, HIDDENCMS_CMS, $this->composer_environment());

		if (!is_resource($process))
		{
			throw new RuntimeException('Composer n\'a pas pu être démarré.');
		}

		fclose($pipes[0]);
		$output = $this->normalize_process_output(stream_get_contents($pipes[1]));
		$error = $this->normalize_process_output(stream_get_contents($pipes[2]));
		fclose($pipes[1]);
		fclose($pipes[2]);
		$code = proc_close($process);

		if ($code !== 0)
		{
			throw new RuntimeException(trim($error ?: $output) ?: 'Échec de l\'installation Composer.');
		}

		return trim($output.PHP_EOL.$error);
	}

	protected function composer_environment()
	{
		$environment = getenv();
		$environment = is_array($environment) ? $environment : [];
		$composer_home = getenv('COMPOSER_HOME');

		if (!$composer_home)
		{
			$composer_home = HIDDENCMS_CMS.'/cache/composer';

			if (!is_dir($composer_home) && !@mkdir($composer_home, 0775, TRUE) && !is_dir($composer_home))
			{
				throw new RuntimeException('Le dossier Composer ne peut pas être créé : '.$composer_home);
			}

			if (!is_writable($composer_home))
			{
				throw new RuntimeException('Le dossier Composer n\'est pas accessible en écriture : '.$composer_home);
			}

			$environment['COMPOSER_HOME'] = $composer_home;
		}

		if (!getenv('HOME'))
		{
			$environment['HOME'] = $composer_home;
		}

		return $environment;
	}

	protected function normalize_process_output($output)
	{
		if ($output === '' || preg_match('//u', $output))
		{
			return $output;
		}

		if (function_exists('mb_convert_encoding'))
		{
			return mb_convert_encoding($output, 'UTF-8', 'Windows-1252');
		}

		return iconv('Windows-1252', 'UTF-8//IGNORE', $output);
	}

	protected function php_binary()
	{
		$candidates = [
			PHP_BINARY,
			rtrim(PHP_BINDIR, '/\\').DIRECTORY_SEPARATOR.(DIRECTORY_SEPARATOR === '\\' ? 'php.exe' : 'php')
		];
		$extension_dir = ini_get('extension_dir');

		if ($extension_dir)
		{
			$candidates[] = rtrim(dirname($extension_dir), '/\\').DIRECTORY_SEPARATOR.(DIRECTORY_SEPARATOR === '\\' ? 'php.exe' : 'php');
		}

		foreach (array_unique($candidates) as $candidate)
		{
			if (is_file($candidate) && in_array(strtolower(basename($candidate)), ['php', 'php.exe'], TRUE))
			{
				return $candidate;
			}
		}

		throw new RuntimeException('Le binaire PHP CLI est introuvable.');
	}

	protected function composer_command()
	{
		$binary = defined('HIDDENCMS_COMPOSER_BINARY') ? HIDDENCMS_COMPOSER_BINARY : 'composer';

		if (strtolower(substr($binary, -5)) === '.phar')
		{
			return [$this->php_binary(), $binary];
		}

		if (DIRECTORY_SEPARATOR === '\\')
		{
			$directories = dirname($binary) !== '.' ? [dirname($binary)] : explode(PATH_SEPARATOR, (string)getenv('PATH'));
			$program_data = getenv('ProgramData') ?: getenv('ALLUSERSPROFILE');

			if (!$program_data)
			{
				$system_drive = getenv('SystemDrive') ?: 'C:';
				$program_data = rtrim($system_drive, '/\\').'\\ProgramData';
			}

			if ($program_data)
			{
				$directories[] = $program_data.'/ComposerSetup/bin';
			}

			foreach (array_unique($directories) as $directory)
			{
				$directory = trim($directory, " \t\n\r\0\x0B\"");

				if ($directory && is_file($phar = $directory.'/composer.phar'))
				{
					return [$this->php_binary(), $phar];
				}
			}
		}

		return [$binary];
	}

	protected function prepare_rollbacks($package)
	{
		$rollbacks = [];

		foreach ($package['migrations'] as $class)
		{
			if (!class_exists($class) || !is_a($migration = new $class, Migration::class))
			{
				throw new RuntimeException('Migration invalide : '.$class);
			}

			$rollbacks[$class] = $migration;
		}

		return $rollbacks;
	}

	protected function rollback_package($package, array $rollbacks)
	{
		$applied = HB()->db->select('migration')
						->from('addon_migrations')
						->where('package', $package['package'])
						->get();

		foreach (array_reverse($package['migrations']) as $class)
		{
			if (!in_array($class, $applied, TRUE))
			{
				continue;
			}

			if (!isset($rollbacks[$class]))
			{
				throw new RuntimeException('Migration invalide : '.$class);
			}

			$migration = $rollbacks[$class];

			HB()->db->begin_transaction();

			try
			{
				$migration->down(HB()->db);
				HB()->db->where('package', $package['package'])
						 ->where('migration', $class)
						 ->delete_checked('addon_migrations');
				HB()->db->commit();
			}
			catch (\Throwable $e)
			{
				HB()->db->rollback();
				throw $e;
			}
		}

		HB()->db->where('package', $package['package'])
				 ->where('migration LIKE', 'seed:%')
				 ->delete_checked('addon_migrations');
	}

	protected function apply_package($package)
	{
		$applied = HB()->db->select('migration')
						->from('addon_migrations')
						->where('package', $package['package'])
						->get();
		$done = [];

		foreach ($package['migrations'] as $class)
		{
			if (!in_array($class, $applied, TRUE))
			{
				$this->run_migration($package['package'], $class);
				$done[] = $class;
			}
		}

		foreach ($package['seeders'] as $class)
		{
			$id = 'seed:'.$class;

			if (!in_array($id, $applied, TRUE))
			{
				$this->run_seeder($package['package'], $class, $id);
				$done[] = $id;
			}
		}

		return $done;
	}

	protected function run_migration($package, $class)
	{
		if (!class_exists($class) || !is_a($migration = new $class, Migration::class))
		{
			throw new RuntimeException('Migration invalide : '.$class);
		}

		$this->run_step($package, $class, function() use ($migration){
			$migration->up(HB()->db);
		});
	}

	protected function run_seeder($package, $class, $id)
	{
		if (!class_exists($class) || !is_a($seeder = new $class, Seeder::class))
		{
			throw new RuntimeException('Seeder invalide : '.$class);
		}

		$this->run_step($package, $id, function() use ($seeder){
			$seeder->run(HB()->db);
		});
	}

	protected function run_step($package, $id, $callback)
	{
		HB()->db->begin_transaction();

		try
		{
			$callback();
			HB()->db->insert_checked('addon_migrations', [
				'package'    => $package,
				'migration'  => $id,
				'applied_at' => date('Y-m-d H:i:s')
			]);
			HB()->db->commit();
		}
		catch (\Throwable $e)
		{
			HB()->db->rollback();
			throw $e;
		}
	}

	protected function ensure_migrations_table()
	{
		HB()->db->execute_checked('CREATE TABLE IF NOT EXISTS `addon_migrations` (
			`package` varchar(190) NOT NULL,
			`migration` varchar(190) NOT NULL,
			`applied_at` datetime NOT NULL,
			PRIMARY KEY (`package`, `migration`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
	}

	protected function class_list($classes)
	{
		return array_values(array_filter((array)$classes, function($class){
			return is_string($class) && $class !== '';
		}));
	}

	protected function is_core_compatible($constraint)
	{
		if (!$constraint)
		{
			return FALSE;
		}

		try
		{
			return class_exists(Semver::class)
				? Semver::satisfies(HIDDENCMS_VERSION, $constraint)
				: version_compare(HIDDENCMS_VERSION, trim($constraint, '^~>=< '), '>=');
		}
		catch (\Throwable $e)
		{
			return FALSE;
		}
	}

	protected function validate_package($package, $constraint = FALSE)
	{
		$pattern = $constraint
			? '#^[a-z0-9](?:[a-z0-9_.-]*[a-z0-9])?/[a-z0-9](?:[a-z0-9_.-]*[a-z0-9])?(?::[^\s]+)?$#i'
			: '#^[a-z0-9](?:[a-z0-9_.-]*[a-z0-9])?/[a-z0-9](?:[a-z0-9_.-]*[a-z0-9])?$#i';

		if (!preg_match($pattern, $package))
		{
			throw new RuntimeException('Nom de paquet Composer invalide.');
		}

		return $package;
	}
}
