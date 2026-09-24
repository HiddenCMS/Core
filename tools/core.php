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
	echo "Migration du core ignorée : HiddenCMS n'est pas encore installé.\n";
	exit(0);
}

define('HIDDENCMS_CLI', TRUE);
require 'index.php';

$command = isset($argv[1]) && substr($argv[1], 0, 2) !== '--' ? $argv[1] : 'status';

try
{
	if ($command === 'status')
	{
		$status = HB()->core_updater->status(in_array('--refresh', $argv, TRUE));
		echo 'HiddenCMS '.$status['core']['current'];

		if ($status['core']['available'])
		{
			echo ' -> '.$status['core']['latest'].' disponible';
		}

		echo PHP_EOL;
	}
	else if ($command === 'updates-check')
	{
		$directory = HIDDENCMS_CMS.'/cache/updates';
		if (!is_dir($directory) && !mkdir($directory, 0775, TRUE) && !is_dir($directory))
		{
			throw new RuntimeException('Could not create the update cache directory.');
		}

		$lock = fopen($directory.'/check.lock', 'c+');
		if (!$lock)
		{
			throw new RuntimeException('Could not create the update check lock.');
		}

		if (!flock($lock, LOCK_EX | LOCK_NB))
		{
			fclose($lock);
			echo "An update check is already running.\n";
			exit(0);
		}

		try
		{
			$status = HB()->core_updater->status(TRUE);
		}
		finally
		{
			flock($lock, LOCK_UN);
			fclose($lock);
		}

		$core_update = !empty($status['core']['available']) ? 'Core '.$status['core']['latest'].' available' : 'Core up to date';
		$addon_count = count(isset($status['addons']) && is_array($status['addons']) ? $status['addons'] : []);
		echo $core_update.', '.$addon_count.' addon update(s).'.PHP_EOL;

		$errors = array_filter([
			isset($status['core']['error']) ? $status['core']['error'] : NULL,
			isset($status['addons_error']) ? $status['addons_error'] : NULL
		]);

		foreach ($errors as $error)
		{
			fwrite(STDERR, $error.PHP_EOL);
		}

		if ($errors)
		{
			exit(1);
		}
	}
	else if ($command === 'migrate')
	{
		$done = HB()->core_updater->migrate();
		echo $done ? count($done).' migration(s) appliquée(s).'.PHP_EOL : "Schéma du core à jour.\n";
	}
	else if ($command === 'backup')
	{
		echo HB()->core_updater->create_backup('cli').PHP_EOL;
	}
	else if ($command === 'privacy-purge')
	{
		$reports = HB()->module('user')->model('privacy')->process_due(isset($argv[2]) ? (int)$argv[2] : 25);
		$errors = array_filter($reports, function($report){ return ($report['core'] ?? '') === 'error'; });
		echo (count($reports) - count($errors)).' compte(s) anonymisé(s), '.count($errors).' échec(s).'.PHP_EOL;
		foreach ($errors as $user_id => $report) fwrite(STDERR, 'Compte #'.$user_id.' : '.$report['error'].PHP_EOL);
		if ($errors) exit(1);
	}
	else if ($command === 'privacy-retention')
	{
		$execute = in_array('--execute', $argv, TRUE);
		$report = HB()->module('settings')->model('retention')->run($execute);
		echo ($execute ? 'Purge' : 'Simulation').' terminée.'.PHP_EOL;
		foreach ($report['candidates'] as $key => $count) echo ' - '.$key.' : '.$count.PHP_EOL;
	}
	else if ($command === 'rollback')
	{
		if (empty($argv[2]))
		{
			throw new RuntimeException('Usage : php tools/core.php rollback identifiant');
		}

		HB()->core_updater->rollback($argv[2]);
		echo 'Sauvegarde '.$argv[2]." restaurée.\n";
	}
	else if ($command === 'update')
	{
		$backup = HB()->core_updater->update_core();
		echo 'Core mis à jour. Point de retour : '.$backup.PHP_EOL;
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
