<?php

namespace HB\HiddenCMS\Libraries;

use Composer\Semver\Semver;
use HB\HiddenCMS\Addons\Migration;
use HB\HiddenCMS\Library;
use RuntimeException;
use Throwable;
use ZipArchive;

class Core_Updater extends Library
{
	const CACHE_TTL = 900;

	public function manifest($root = HIDDENCMS_CMS)
	{
		$file = rtrim(str_replace('\\', '/', $root), '/').'/hiddencms.json';
		$data = is_file($file) ? json_decode(file_get_contents($file), TRUE) : NULL;

		if (!is_array($data) || empty($data['name']) || empty($data['version']))
		{
			throw new RuntimeException('Le manifeste HiddenCMS est absent ou invalide.');
		}

		return $data;
	}

	public function status($refresh = FALSE)
	{
		$cache = HIDDENCMS_CMS.'/cache/updates/status.json';

		if (!$refresh && is_file($cache) && filemtime($cache) >= time() - self::CACHE_TTL)
		{
			if (is_array($status = json_decode(file_get_contents($cache), TRUE)))
			{
				return $this->official_status($status);
			}
		}

		$status = [
			'checked_at' => date('c'),
			'core' => [
				'current'   => HIDDENCMS_VERSION,
				'latest'    => NULL,
				'available' => FALSE,
				'release'   => NULL,
				'error'     => NULL
			],
			'addons' => [],
			'compatibility' => []
		];

		try
		{
			$release = $this->latest_release();
			$latest = ltrim(isset($release['tag_name']) ? $release['tag_name'] : '', 'vV');
			$status['core']['latest'] = $latest ?: NULL;
			$status['core']['available'] = $latest && version_compare($latest, HIDDENCMS_VERSION, '>');
			$status['core']['release'] = [
				'name'         => isset($release['name']) ? $release['name'] : $latest,
				'published_at' => isset($release['published_at']) ? $release['published_at'] : NULL,
				'url'          => isset($release['html_url']) ? $release['html_url'] : NULL,
				'zip'          => isset($release['zipball_url']) ? $release['zipball_url'] : NULL,
				'notes'        => isset($release['body']) ? $release['body'] : ''
			];
		}
		catch (Throwable $e)
		{
			$status['core']['error'] = $e->getMessage();
		}

		try
		{
			$status['addons'] = $this->addon_packages->outdated();
		}
		catch (Throwable $e)
		{
			$status['addons_error'] = $e->getMessage();
		}

		$status['compatibility'] = $this->addon_packages->compatibility();
		$status = $this->official_status($status);
		$this->write_json($cache, $status);

		return $status;
	}

	public function clear_status_cache()
	{
		$cache = HIDDENCMS_CMS.'/cache/updates/status.json';

		if (is_file($cache))
		{
			@unlink($cache);
		}

		return $this;
	}

	protected function official_status(array $status)
	{
		$status['addons'] = array_filter(isset($status['addons']) && is_array($status['addons']) ? $status['addons'] : [], function($package){
			return !empty($package['package']) && strpos(strtolower($package['package']), 'hiddencms/') === 0;
		});
		$status['compatibility'] = array_filter(isset($status['compatibility']) && is_array($status['compatibility']) ? $status['compatibility'] : [], function($package){
			return strpos(strtolower($package), 'hiddencms/') === 0;
		}, ARRAY_FILTER_USE_KEY);

		return $status;
	}

	public function migrate()
	{
		$this->ensure_migrations_table();
		$applied = HB()->db->select('migration')->from('core_migrations')->get();
		$files = glob(HIDDENCMS_CMS.'/hiddencms/migrations/*.php') ?: [];
		usort($files, function($a, $b){
			return version_compare(pathinfo($a, PATHINFO_FILENAME), pathinfo($b, PATHINFO_FILENAME));
		});
		$batch = (int)HB()->db->select('MAX(batch)')->from('core_migrations')->row() + 1;
		$done = [];

		foreach ($files as $file)
		{
			$id = basename($file, '.php');

			if (in_array($id, $applied, TRUE))
			{
				continue;
			}

			$migration = require $file;

			if (!$migration instanceof Migration)
			{
				throw new RuntimeException('Migration core invalide : '.$id);
			}

			HB()->db->begin_transaction();

			try
			{
				$migration->up(HB()->db);
				HB()->db->insert_checked('core_migrations', [
					'migration' => $id,
					'version'   => HIDDENCMS_SCHEMA_VERSION,
					'batch'     => $batch,
					'applied_at'=> date('Y-m-d H:i:s')
				]);
				HB()->db->commit();
				$done[] = $id;
			}
			catch (Throwable $e)
			{
				HB()->db->rollback();
				throw $e;
			}
		}

		return $done;
	}

	public function update_core(array $release = NULL)
	{
		if (!$release)
		{
			try
			{
				$release = $this->latest_release();
			}
			catch (Throwable $e)
			{
				$failure = [
					'at'      => date('c'),
					'stage'   => 'recherche de la release',
					'class'   => get_class($e),
					'message' => $this->failure_message($e)
				];
				$this->record_update_failure(NULL, NULL, $failure);
				$this->log_update_failure(NULL, NULL, $failure);
				throw new RuntimeException('La recherche de la mise à jour a échoué : '.$failure['message'], 0, $e);
			}
		}

		$url = isset($release['zipball_url']) ? $release['zipball_url'] : NULL;
		$version = ltrim(isset($release['tag_name']) ? $release['tag_name'] : '', 'vV');
		$stage = 'validation';

		if (!$url || !$version || !version_compare($version, HIDDENCMS_VERSION, '>'))
		{
			throw new RuntimeException('Aucune mise à jour du core n\'est disponible.');
		}

		$work = HIDDENCMS_CMS.'/cache/updates/work-'.bin2hex(random_bytes(6));
		$archive = $work.'/release.zip';
		$extract = $work.'/release';
		$this->ensure_directory($extract);
		$backup = NULL;
		$maintenance = (bool)$this->config->maintenance;
		$addon_requirements = $this->addon_requirements();

		try
		{
			$stage = 'téléchargement';
			$this->download($url, $archive);
			$stage = 'extraction';
			$this->extract_archive($archive, $extract);
			$source = $this->find_release_root($extract);
			$manifest = $this->manifest($source);
			$this->validate_release($manifest, $version);
			$stage = 'sauvegarde';
			$backup = $this->create_backup('core-'.$version);
			$this->mark_backup($backup, 'updating', $version);
			$this->config('maintenance', TRUE, 'bool');
			$stage = 'copie des fichiers';
			$this->copy_release($source, $manifest);
			$this->restore_addon_requirements($addon_requirements);
			$stage = 'dépendances Composer';
			$this->addon_packages->update_dependencies(array_keys($addon_requirements));
			$stage = 'migrations SQL';
			$this->migrate();
			$stage = 'synchronisation des addons';
			$this->addon_packages->sync(TRUE);
			$this->mark_backup($backup, 'completed', $version);
			$this->config('maintenance', $maintenance, 'bool');
			$this->remove_directory($work);
			$this->clear_update_failure();
			$this->clear_status_cache();

			return $backup;
		}
		catch (Throwable $e)
		{
			$failure = [
				'at'      => date('c'),
				'stage'   => $stage,
				'class'   => get_class($e),
				'message' => $this->failure_message($e)
			];
			$rollback_error = NULL;

			if ($backup)
			{
				try
				{
					$this->rollback($backup, TRUE);
					try
					{
						$this->mark_backup($backup, 'automatic-rollback', $version, ['failure' => $failure]);
					}
					catch (Throwable $metadata_error) {}
				}
				catch (Throwable $rollback)
				{
					$rollback_error = $this->failure_message($rollback);
					try
					{
						$this->mark_backup($backup, 'rollback-failed', $version, [
							'failure'       => $failure,
							'rollback_error'=> $rollback_error
						]);
					}
					catch (Throwable $metadata_error) {}
				}
			}

			$this->config('maintenance', $maintenance, 'bool');
			$this->remove_directory($work);
			$this->record_update_failure($version, $backup, $failure, $rollback_error);
			$this->log_update_failure($version, $backup, $failure, $rollback_error);

			$message = 'La mise à jour vers '.$version.' a échoué à l’étape « '.$stage.' » : '.$failure['message'];
			$message .= $rollback_error
				? ' Le retour arrière automatique a également échoué : '.$rollback_error
				: ($backup ? ' Le site a été restauré automatiquement.' : ' Aucun fichier du site n’a été remplacé.');
			throw new RuntimeException($message, 0, $e);
		}
	}

	public function create_backup($reason = 'manual')
	{
		if (!class_exists(ZipArchive::class))
		{
			throw new RuntimeException('L\'extension PHP Zip est requise pour créer une sauvegarde.');
		}

		$id = date('Ymd-His').'-'.bin2hex(random_bytes(3));
		$directory = HIDDENCMS_CMS.'/backups/updates/'.$id;
		$this->ensure_directory($directory);

		try
		{
			$sql = $directory.'/database.sql';
			$handle = fopen($sql, 'wb');

			if (!$handle)
			{
				throw new RuntimeException('Impossible de créer la sauvegarde SQL.');
			}

			$this->mysqldump('default')->dump($handle);
			$files = $this->managed_files();
			$zip = new ZipArchive();

			if ($zip->open($directory.'/files.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE)
			{
				throw new RuntimeException('Impossible de créer l\'archive de sauvegarde.');
			}

			foreach ($files as $file)
			{
				$zip->addFile(HIDDENCMS_CMS.'/'.$file, $file);
			}

			$zip->close();
			$this->write_json($directory.'/metadata.json', [
				'id'            => $id,
				'reason'        => $reason,
				'created_at'    => date('c'),
				'core_version'  => HIDDENCMS_VERSION,
				'schema_version'=> HIDDENCMS_SCHEMA_VERSION,
				'maintenance'   => (bool)$this->config->maintenance,
				'status'        => 'ready',
				'files'         => $files
			]);
		}
		catch (Throwable $e)
		{
			$this->remove_directory($directory);
			throw $e;
		}

		return $id;
	}

	public function backups()
	{
		$result = [];

		foreach (glob(HIDDENCMS_CMS.'/backups/updates/*/metadata.json') ?: [] as $file)
		{
			if (is_array($metadata = json_decode(file_get_contents($file), TRUE)))
			{
				$result[$metadata['id']] = $metadata;
			}
		}

		krsort($result);
		return $result;
	}

	public function last_failure()
	{
		$file = HIDDENCMS_CMS.'/cache/updates/last-error.json';
		$content = is_file($file) ? @file_get_contents($file) : FALSE;
		$data = is_string($content) ? json_decode($content, TRUE) : NULL;
		return is_array($data) && !empty($data['failure']) ? $data : NULL;
	}

	public function rollback($id, $automatic = FALSE)
	{
		if (!preg_match('/^[a-z0-9-]+$/i', $id))
		{
			throw new RuntimeException('Identifiant de sauvegarde invalide.');
		}

		$directory = HIDDENCMS_CMS.'/backups/updates/'.$id;
		$metadata_file = $directory.'/metadata.json';
		$metadata = is_file($metadata_file) ? json_decode(file_get_contents($metadata_file), TRUE) : NULL;

		if (!is_array($metadata) || !is_file($directory.'/files.zip') || !is_file($directory.'/database.sql'))
		{
			throw new RuntimeException('La sauvegarde demandée est incomplète.');
		}

		$restore_maintenance = !empty($metadata['maintenance']);
		$this->config('maintenance', TRUE, 'bool');

		try
		{
			$before = array_flip(isset($metadata['files']) ? $metadata['files'] : []);

			foreach ($this->managed_files() as $file)
			{
				if (!isset($before[$file]))
				{
					@unlink(HIDDENCMS_CMS.'/'.$file);
				}
			}

			$this->restore_files_archive($directory.'/files.zip');
			HB()->db->execute_script(file_get_contents($directory.'/database.sql'));
			$this->addon_packages->install_dependencies();
			$this->config('maintenance', $restore_maintenance, 'bool');
			$this->mark_backup($id, $automatic ? 'automatic-rollback' : 'restored');
		}
		catch (Throwable $e)
		{
			$this->config('maintenance', $restore_maintenance, 'bool');
			throw $e;
		}

		return $metadata;
	}

	protected function latest_release()
	{
		try
		{
			$data = json_decode($this->request(HIDDENCMS_VERSION_CHECK_URL), TRUE);
		}
		catch (Throwable $api_error)
		{
			return $this->latest_release_from_feed($api_error);
		}

		if (!is_array($data) || empty($data['tag_name']) || !empty($data['draft']))
		{
			return $this->latest_release_from_feed();
		}

		return $data;
	}

	protected function latest_release_from_feed(Throwable $previous = NULL)
	{
		try
		{
			$xml = @simplexml_load_string($this->request(HIDDENCMS_UPDATE_BASE.'/releases.atom'));

			if (!$xml || empty($xml->entry[0]))
			{
				throw new RuntimeException('Aucune release stable de HiddenCMS n\'est publiée pour le moment.');
			}

			$entry = $xml->entry[0];
			$attributes = $entry->link->attributes();
			$url = isset($attributes['href']) ? (string)$attributes['href'] : '';
			$tag = preg_match('#/tag/([^/]+)$#', $url, $match) ? rawurldecode($match[1]) : trim((string)$entry->title);

			if (!$tag)
			{
				throw new RuntimeException('Le flux des releases HiddenCMS est invalide.');
			}

			return [
				'tag_name'     => $tag,
				'name'         => trim((string)$entry->title) ?: $tag,
				'published_at' => (string)$entry->updated,
				'html_url'     => $url,
				'zipball_url'  => HIDDENCMS_UPDATE_BASE.'/archive/refs/tags/'.rawurlencode($tag).'.zip',
				'body'         => trim(strip_tags((string)$entry->content)),
				'draft'        => FALSE
			];
		}
		catch (Throwable $feed_error)
		{
			if ($feed_error instanceof RuntimeException && strpos($feed_error->getMessage(), 'Aucune release stable') === 0)
			{
				throw $feed_error;
			}

			throw new RuntimeException('Impossible de joindre le service de mises à jour.', 0, $previous ?: $feed_error);
		}
	}

	protected function request($url)
	{
		if (function_exists('curl_init'))
		{
			$curl = curl_init($url);
			curl_setopt_array($curl, [
				CURLOPT_RETURNTRANSFER => TRUE,
				CURLOPT_FOLLOWLOCATION => TRUE,
				CURLOPT_CONNECTTIMEOUT => 8,
				CURLOPT_TIMEOUT => 20,
				CURLOPT_USERAGENT => 'HiddenCMS/'.HIDDENCMS_VERSION,
				CURLOPT_HTTPHEADER => ['Accept: application/vnd.github+json']
			]);

			if (defined('CURL_IPRESOLVE_V4'))
			{
				curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
			}
			$content = curl_exec($curl);
			$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$error = curl_error($curl);
			curl_close($curl);

			if ($content === FALSE || $code >= 400)
			{
				throw new RuntimeException($code >= 400
					? 'Le service de mises à jour a répondu avec le statut '.$code.'.'
					: 'Impossible de joindre le service de mises à jour.');
			}

			return $content;
		}

		$context = stream_context_create(['http' => [
			'timeout' => 20,
			'header' => "User-Agent: HiddenCMS/".HIDDENCMS_VERSION."\r\nAccept: application/vnd.github+json\r\n"
		]]);
		$content = @file_get_contents($url, FALSE, $context);

		if ($content === FALSE)
		{
			throw new RuntimeException('Impossible de joindre le service de mises à jour.');
		}

		return $content;
	}

	protected function download($url, $target)
	{
		$this->ensure_directory(dirname($target));
		$content = $this->request($url);

		if (file_put_contents($target, $content) === FALSE)
		{
			throw new RuntimeException('Impossible d\'enregistrer l\'archive de mise à jour.');
		}
	}

	protected function validate_release(array $manifest, $release_version)
	{
		if ($manifest['name'] !== 'hiddencms/core' || $manifest['version'] !== $release_version)
		{
			throw new RuntimeException('La release ne correspond pas au manifeste annoncé.');
		}

		if (!version_compare($manifest['version'], HIDDENCMS_VERSION, '>'))
		{
			throw new RuntimeException('La release n\'est pas plus récente que le core installé.');
		}

		if (!empty($manifest['php']) && class_exists(Semver::class) && !Semver::satisfies(PHP_VERSION, $manifest['php']))
		{
			throw new RuntimeException('Cette version de HiddenCMS nécessite PHP '.$manifest['php'].'.');
		}
	}

	protected function copy_release($source, array $manifest)
	{
		$protected = $this->protected_paths($manifest);
		$installer = $source.'/install/index.php';
		$installed = HIDDENCMS_CMS.'/install/installed.txt';

		if (is_file($installer))
		{
			$this->ensure_directory(dirname($installed));

			if (!is_file($installed) && !@touch($installed))
			{
				throw new RuntimeException('Impossible de sécuriser le répertoire d\'installation.');
			}
		}

		$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS));

		foreach ($iterator as $file)
		{
			if (!$file->isFile())
			{
				continue;
			}

			$relative = str_replace('\\', '/', substr($file->getPathname(), strlen($source) + 1));

			if ($this->is_protected($relative, $protected))
			{
				continue;
			}

			$target = HIDDENCMS_CMS.'/'.$relative;
			$this->ensure_directory(dirname($target));

			if (!copy($file->getPathname(), $target))
			{
				throw new RuntimeException('Impossible de remplacer '.$relative.'.');
			}
		}

		foreach (isset($manifest['update']['remove']) ? $manifest['update']['remove'] : [] as $relative)
		{
			$relative = trim(str_replace('\\', '/', $relative), '/');

			if ($relative && !$this->is_protected($relative, $protected) && strpos($relative, '..') === FALSE)
			{
				$target = HIDDENCMS_CMS.'/'.$relative;
				is_dir($target) ? $this->remove_directory($target) : @unlink($target);
			}
		}
	}

	protected function managed_files()
	{
		$manifest = $this->manifest();
		$protected = $this->protected_paths($manifest);
		$result = [];
		$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(HIDDENCMS_CMS, \FilesystemIterator::SKIP_DOTS));

		foreach ($iterator as $file)
		{
			if (!$file->isFile())
			{
				continue;
			}

			$relative = str_replace('\\', '/', substr($file->getPathname(), strlen(HIDDENCMS_CMS) + 1));

			if (!$this->is_protected($relative, $protected))
			{
				$result[] = $relative;
			}
		}

		sort($result);
		return $result;
	}

	protected function protected_paths(array $manifest)
	{
		$paths = isset($manifest['update']['protected']) ? $manifest['update']['protected'] : [];
		return array_values(array_unique(array_merge($paths, ['.git', 'backups', 'cache', 'install/installed.txt', 'logs', 'upload', 'uploads', 'vendor'])));
	}

	protected function is_protected($relative, array $paths)
	{
		foreach ($paths as $path)
		{
			$path = trim(str_replace('\\', '/', $path), '/');

			if ($relative === $path || strpos($relative, $path.'/') === 0)
			{
				return TRUE;
			}
		}

		return FALSE;
	}

	protected function extract_archive($archive, $destination)
	{
		$zip = new ZipArchive();

		if ($zip->open($archive) !== TRUE)
		{
			throw new RuntimeException('L\'archive de mise à jour est illisible.');
		}

		for ($i = 0; $i < $zip->numFiles; $i++)
		{
			$name = str_replace('\\', '/', $zip->getNameIndex($i));

			if ($name === '' || $name[0] === '/' || preg_match('#(^|/)\.\.(/|$)#', $name))
			{
				$zip->close();
				throw new RuntimeException('L\'archive contient un chemin non autorisé.');
			}
		}

		$this->ensure_directory($destination);

		if (!$zip->extractTo($destination))
		{
			$zip->close();
			throw new RuntimeException('Impossible d\'extraire l\'archive de mise à jour.');
		}

		$zip->close();
	}

	protected function restore_files_archive($archive)
	{
		$zip = new ZipArchive();

		if ($zip->open($archive) !== TRUE)
		{
			throw new RuntimeException('L\'archive de sauvegarde est illisible.');
		}

		$protected = $this->protected_paths($this->manifest());

		for ($i = 0; $i < $zip->numFiles; $i++)
		{
			$relative = str_replace('\\', '/', $zip->getNameIndex($i));

			if ($relative === '' || substr($relative, -1) === '/' || $relative[0] === '/' || preg_match('#(^|/)\.\.(/|$)#', $relative) || $this->is_protected($relative, $protected))
			{
				continue;
			}

			$source = $zip->getStream($zip->getNameIndex($i));
			$target = HIDDENCMS_CMS.'/'.$relative;
			$this->ensure_directory(dirname($target));
			$destination = fopen($target, 'wb');

			if (!$source || !$destination)
			{
				is_resource($source) && fclose($source);
				is_resource($destination) && fclose($destination);
				$zip->close();
				throw new RuntimeException('Impossible de restaurer '.$relative.'.');
			}

			stream_copy_to_stream($source, $destination);
			fclose($source);
			fclose($destination);
		}

		$zip->close();
	}

	protected function find_release_root($directory)
	{
		if (is_file($directory.'/hiddencms.json'))
		{
			return $directory;
		}

		foreach (glob($directory.'/*', GLOB_ONLYDIR) ?: [] as $candidate)
		{
			if (is_file($candidate.'/hiddencms.json'))
			{
				return $candidate;
			}
		}

		throw new RuntimeException('Le manifeste de la release est introuvable.');
	}

	protected function ensure_migrations_table()
	{
		HB()->db->execute_checked('CREATE TABLE IF NOT EXISTS `core_migrations` (
			`migration` varchar(190) NOT NULL,
			`version` varchar(50) NOT NULL,
			`batch` int unsigned NOT NULL,
			`applied_at` datetime NOT NULL,
			PRIMARY KEY (`migration`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
	}

	protected function mark_backup($id, $status, $target = NULL, array $extra = [])
	{
		$file = HIDDENCMS_CMS.'/backups/updates/'.$id.'/metadata.json';
		$metadata = is_file($file) ? json_decode(file_get_contents($file), TRUE) : [];
		$metadata['status'] = $status;
		$metadata['updated_at'] = date('c');

		if ($target)
		{
			$metadata['target_version'] = $target;
		}

		foreach ($extra as $key => $value)
		{
			$metadata[$key] = $value;
		}

		$this->write_json($file, $metadata);
	}

	protected function failure_message(Throwable $error)
	{
		$message = preg_replace('/\s+/u', ' ', $error->getMessage());
		$message = trim($message === NULL ? $error->getMessage() : $message);
		return function_exists('mb_substr') ? mb_substr($message, 0, 8000) : substr($message, 0, 8000);
	}

	protected function addon_requirements()
	{
		$file = HIDDENCMS_CMS.'/composer.json';
		$composer = is_file($file) ? json_decode(file_get_contents($file), TRUE) : NULL;
		$requirements = [];

		foreach (is_array($composer) && isset($composer['require']) && is_array($composer['require']) ? $composer['require'] : [] as $package => $constraint)
		{
			if (strpos(strtolower($package), 'hiddencms/') === 0 && strtolower($package) !== 'hiddencms/core')
			{
				$requirements[$package] = $constraint;
			}
		}

		ksort($requirements);
		return $requirements;
	}

	protected function restore_addon_requirements(array $requirements)
	{
		if (!$requirements) return;

		$file = HIDDENCMS_CMS.'/composer.json';
		$composer = is_file($file) ? json_decode(file_get_contents($file), TRUE) : NULL;

		if (!is_array($composer))
		{
			throw new RuntimeException('Le manifeste Composer du core mis à jour est invalide.');
		}

		$composer['require'] = array_merge(isset($composer['require']) && is_array($composer['require']) ? $composer['require'] : [], $requirements);
		ksort($composer['require']);
		$this->write_json($file, $composer);
	}

	protected function log_update_failure($version, $backup, array $failure, $rollback_error = NULL)
	{
		$directory = HIDDENCMS_CMS.'/logs';
		if (!is_dir($directory) && !@mkdir($directory, 0775, TRUE) && !is_dir($directory)) return;
		$entry = '['.date('c').'] Core update to '.($version ?: 'unknown version').' failed during '.$failure['stage'].PHP_EOL
			.'Exception: '.$failure['class'].': '.$failure['message'].PHP_EOL
			.'Backup: '.($backup ?: 'none').PHP_EOL
			.'Rollback: '.($rollback_error ? 'failed: '.$rollback_error : ($backup ? 'completed' : 'not required')).PHP_EOL
			.str_repeat('-', 72).PHP_EOL;
		@file_put_contents($directory.'/core-updater.log', $entry, FILE_APPEND | LOCK_EX);
	}

	protected function record_update_failure($version, $backup, array $failure, $rollback_error = NULL)
	{
		$directory = HIDDENCMS_CMS.'/cache/updates';
		if (!is_dir($directory) && !@mkdir($directory, 0775, TRUE) && !is_dir($directory)) return;
		$data = [
			'target_version' => $version,
			'backup'         => $backup,
			'failure'        => $failure,
			'rollback_error' => $rollback_error
		];
		@file_put_contents($directory.'/last-error.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
	}

	protected function clear_update_failure()
	{
		$file = HIDDENCMS_CMS.'/cache/updates/last-error.json';
		if (is_file($file)) @unlink($file);
	}

	protected function write_json($file, array $data)
	{
		$this->ensure_directory(dirname($file));

		if (file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) === FALSE)
		{
			throw new RuntimeException('Impossible d\'écrire '.$file.'.');
		}
	}

	protected function ensure_directory($directory)
	{
		if (!is_dir($directory) && !mkdir($directory, 0775, TRUE) && !is_dir($directory))
		{
			throw new RuntimeException('Impossible de créer le dossier '.$directory.'.');
		}
	}

	protected function remove_directory($directory)
	{
		if (!is_dir($directory))
		{
			return;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
			\RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ($iterator as $file)
		{
			$file->isDir() ? @rmdir($file->getPathname()) : @unlink($file->getPathname());
		}

		@rmdir($directory);
	}
}
