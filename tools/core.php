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
	else if ($command === 'migrate')
	{
		$done = HB()->core_updater->migrate();
		echo $done ? count($done).' migration(s) appliquée(s).'.PHP_EOL : "Schéma du core à jour.\n";
	}
	else if ($command === 'backup')
	{
		echo HB()->core_updater->create_backup('cli').PHP_EOL;
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
