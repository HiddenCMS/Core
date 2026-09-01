<?php

$root = dirname(__DIR__);
chdir($root);

$composer_hook = in_array('--composer-hook', $argv, TRUE);

if (!is_file('vendor/autoload.php'))
{
	fwrite(STDERR, "Les dépendances Composer ne sont pas installées.\n");
	exit($composer_hook ? 0 : 1);
}

if (!is_file('config/db.php') || (is_file('install/index.php') && !is_file('install/installed.txt')))
{
	echo "Synchronisation des addons ignorée : HiddenCMS n'est pas encore installé.\n";
	exit(0);
}

define('HIDDENCMS_CLI', TRUE);
require 'index.php';

$command = isset($argv[1]) && substr($argv[1], 0, 2) !== '--' ? $argv[1] : 'sync';
$manager = HB()->addon_packages;

try
{
	if ($command === 'discover' || $command === 'status')
	{
		$packages = $manager->discover();

		if (!$packages)
		{
			echo "Aucun addon Composer détecté.\n";
		}
		else
		{
			foreach ($packages as $package)
			{
				$addons = array_map(function($addon){
					return $addon['type'].':'.$addon['name'];
				}, $package['addons']);

				echo $package['package'].' '.$package['version'].' ['.implode(', ', $addons)."]\n";
			}
		}
	}
	else if ($command === 'sync')
	{
		$results = $manager->sync(TRUE);
		echo count($results).' addon(s) Composer synchronisé(s).'.PHP_EOL;

		foreach ($results as $package => $result)
		{
			echo '- '.$package;

			if ($result['applied'])
			{
				echo ' : '.count($result['applied']).' étape(s) appliquée(s)';
			}

			echo PHP_EOL;
		}
	}
	else if ($command === 'require')
	{
		if (empty($argv[2]))
		{
			throw new RuntimeException('Usage : php tools/addons.php require vendor/package[:contrainte]');
		}

		echo $manager->require_package($argv[2]).PHP_EOL;
	}
	else if ($command === 'update')
	{
		if (empty($argv[2]))
		{
			throw new RuntimeException('Usage : php tools/addons.php update vendor/package');
		}

		echo $manager->update_package($argv[2]).PHP_EOL;
	}
	else if ($command === 'remove')
	{
		if (empty($argv[2]))
		{
			throw new RuntimeException('Usage : php tools/addons.php remove vendor/package [--purge]');
		}

		$purge = in_array('--purge', $argv, TRUE);
		echo $manager->remove_package($argv[2], $purge).PHP_EOL;
	}
	else
	{
		throw new RuntimeException('Commande inconnue : '.$command);
	}
}
catch (Throwable $e)
{
	fwrite(STDERR, $e->getMessage().PHP_EOL);
	exit(1);
}
