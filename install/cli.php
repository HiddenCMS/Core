<?php

if (PHP_SAPI != 'cli')
{
	exit("This installer must be run from the command line.\n");
}

define('HIDDENCMS_CLI_INSTALLER', TRUE);
define('HIDDENCMS_ROOT', dirname(__DIR__));

chdir(HIDDENCMS_ROOT);

main($argv);

function main($argv)
{
	$options = parse_options($argv);

	if (isset($options['help']))
	{
		print_usage();
		exit(0);
	}

	ensure_required_extensions(['mysqli', 'mbstring']);

	$config = [
		'db_host'       => option($options, 'db-host', 'localhost'),
		'db_port'       => (int)option($options, 'db-port', 3306),
		'db_name'       => option($options, 'db-name'),
		'db_user'       => option($options, 'db-user', 'root'),
		'db_pass'       => option($options, 'db-pass', ''),
		'create_db'     => isset($options['create-db']),
		'admin_user'    => option($options, 'admin-user', 'admin'),
		'admin_pass'    => option($options, 'admin-pass'),
		'admin_email'   => option($options, 'admin-email'),
		'base'          => option($options, 'base', '/'),
		'site_name'     => option($options, 'site-name'),
		'site_contact'  => option($options, 'site-contact'),
		'force'         => isset($options['force']),
		'yes'           => isset($options['yes']),
		'no_htaccess'   => isset($options['no-htaccess']),
		'remove_install' => isset($options['remove-installer'])
	];

	if (isset($options['admin-pass-env']))
	{
		$config['admin_pass'] = getenv($options['admin-pass-env']) ?: $config['admin_pass'];
	}

	interactive_config($config);
	validate_config($config);

	line('HiddenCMS command line installer');
	line('Database: '.$config['db_user'].'@'.$config['db_host'].':'.$config['db_port'].'/'.$config['db_name']);

	if (!$config['yes'] && !confirm('Continue installation?', TRUE))
	{
		exit_with_error('Installation cancelled.');
	}

	$mysqli = connect_database($config);

	if (has_hiddencms_tables($mysqli) && !$config['force'])
	{
		exit_with_error('This database already contains HiddenCMS tables. Re-run with --force to reinstall.');
	}

	write_db_config($config);
	import_database($mysqli, HIDDENCMS_ROOT.'/install/DATABASE.sql');
	configure_site($mysqli, $config);
	configure_admin($mysqli, $config);
	write_htaccess($config);
	mark_install_complete();

	if ($config['remove_install'])
	{
		remove_directory(HIDDENCMS_ROOT.'/install');
	}

	line('');
	line('Installation complete.');
	line('Admin account: '.$config['admin_user'].' <'.$config['admin_email'].'>');

	if (!$config['remove_install'])
	{
		line('The install directory was kept for CLI reuse and the web installer was disabled.');
		line('Use --remove-installer if you want the CLI installer to delete it after installation.');
	}
}

function parse_options($argv)
{
	$options = [];
	$value_options = [
		'db-host',
		'db-port',
		'db-name',
		'db-user',
		'db-pass',
		'admin-user',
		'admin-pass',
		'admin-pass-env',
		'admin-email',
		'base',
		'site-name',
		'site-contact'
	];
	$flag_options = [
		'create-db',
		'force',
		'yes',
		'no-htaccess',
		'remove-installer',
		'help'
	];

	foreach (array_slice($argv, 1) as $arg)
	{
		if (substr($arg, 0, 2) != '--')
		{
			exit_with_error('Unknown argument: '.$arg);
		}

		$arg = substr($arg, 2);

		if (strpos($arg, '=') !== FALSE)
		{
			list($name, $value) = explode('=', $arg, 2);
		}
		else
		{
			$name = $arg;
			$value = TRUE;
		}

		if (!in_array($name, $value_options) && !in_array($name, $flag_options))
		{
			exit_with_error('Unknown option: --'.$name);
		}

		if (in_array($name, $value_options) && $value === TRUE)
		{
			exit_with_error('Option --'.$name.' requires a value.');
		}

		$options[$name] = $value;
	}

	return $options;
}

function option($options, $name, $default = NULL)
{
	return array_key_exists($name, $options) ? $options[$name] : $default;
}

function interactive_config(&$config)
{
	if (!$config['db_name'])
	{
		$config['db_name'] = prompt('Database name', 'hiddencms');
	}

	if (!$config['admin_email'])
	{
		$config['admin_email'] = prompt('Admin email', 'admin@example.test');
	}

	if ($config['admin_pass'] === NULL)
	{
		$password = prompt('Admin password (leave empty to generate one)', '');

		if ($password === '')
		{
			$password = generate_password();
			line('Generated admin password: '.$password);
		}

		$config['admin_pass'] = $password;
	}
}

function validate_config($config)
{
	if ($config['db_host'] === '' || $config['db_name'] === '' || $config['db_user'] === '')
	{
		exit_with_error('Database host, name and user are required.');
	}

	if ($config['admin_user'] === '' || $config['admin_pass'] === '')
	{
		exit_with_error('Admin username and password are required.');
	}

	if (!filter_var($config['admin_email'], FILTER_VALIDATE_EMAIL))
	{
		exit_with_error('Invalid admin email.');
	}

	if ($config['base'] === '')
	{
		exit_with_error('Rewrite base cannot be empty.');
	}

	if ($config['base'][0] != '/')
	{
		$config['base'] = '/'.$config['base'];
	}
}

function ensure_required_extensions($extensions)
{
	foreach ($extensions as $extension)
	{
		if (!extension_loaded($extension))
		{
			exit_with_error('Missing required PHP extension: '.$extension);
		}
	}
}

function connect_database($config)
{
	$mysqli = mysqli_init();
	$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);

	if (!@$mysqli->real_connect($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name'], $config['db_port']))
	{
		if ($config['create_db'] && $mysqli->connect_errno == 1049)
		{
			$mysqli = create_database($config);
		}
		else
		{
			exit_with_error('Database connection failed: '.$mysqli->connect_error);
		}
	}

	$mysqli->set_charset('utf8');

	return $mysqli;
}

function create_database($config)
{
	$mysqli = mysqli_init();
	$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);

	if (!@$mysqli->real_connect($config['db_host'], $config['db_user'], $config['db_pass'], '', $config['db_port']))
	{
		exit_with_error('Database connection failed: '.$mysqli->connect_error);
	}

	if (!$mysqli->query('CREATE DATABASE `'.escape_identifier($config['db_name']).'` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci'))
	{
		exit_with_error('Database creation failed: '.$mysqli->error);
	}

	if (!$mysqli->select_db($config['db_name']))
	{
		exit_with_error('Unable to select created database: '.$mysqli->error);
	}

	$mysqli->set_charset('utf8');
	line('Created database '.$config['db_name']);

	return $mysqli;
}

function has_hiddencms_tables($mysqli)
{
	$tables = expected_tables();

	if (!$result = $mysqli->query('SHOW TABLE STATUS LIKE "%"'))
	{
		exit_with_error('Unable to inspect database tables: '.$mysqli->error);
	}

	while ($table = $result->fetch_object())
	{
		if (in_array($table->Name, $tables))
		{
			$result->close();
			return TRUE;
		}
	}

	$result->close();

	return FALSE;
}

function expected_tables()
{
	if (!preg_match_all('/^DROP TABLE IF EXISTS `(.+?)`;/m', file_get_contents(HIDDENCMS_ROOT.'/install/DATABASE.sql'), $matches))
	{
		exit_with_error('Unable to read expected tables from install/DATABASE.sql.');
	}

	return $matches[1];
}

function write_db_config($config)
{
	$content = "<?php\n\n";
	$content .= "\$db[] = [\n";
	$content .= "\t'hostname' => '".escape_php($config['db_host'])."',\n";
	$content .= "\t'username' => '".escape_php($config['db_user'])."',\n";
	$content .= "\t'password' => '".escape_php($config['db_pass'])."',\n";
	$content .= "\t'database' => '".escape_php($config['db_name'])."',\n";
	$content .= "\t'driver'   => 'mysqli'\n";
	$content .= "];\n";

	write_file(HIDDENCMS_ROOT.'/config/db.php', $content);

	line('Wrote config/db.php');
}

function import_database($mysqli, $file)
{
	if (!is_file($file))
	{
		exit_with_error('Database file not found: '.$file);
	}

	$queries = split_sql(file_get_contents($file));
	$count = 0;

	foreach ($queries as $query)
	{
		if (trim($query) === '')
		{
			continue;
		}

		if (!$mysqli->query($query))
		{
			exit_with_error('SQL import failed near query #'.($count + 1).': '.$mysqli->error);
		}

		$count++;
	}

	line('Imported '.$count.' SQL queries');
}

function split_sql($sql)
{
	$queries = [];
	$current = '';
	$quote = NULL;
	$line_comment = FALSE;
	$block_comment = FALSE;
	$length = strlen($sql);

	for ($i = 0; $i < $length; $i++)
	{
		$char = $sql[$i];
		$next = $i + 1 < $length ? $sql[$i + 1] : '';

		if ($line_comment)
		{
			$current .= $char;

			if ($char == "\n")
			{
				$line_comment = FALSE;
			}

			continue;
		}

		if ($block_comment)
		{
			$current .= $char;

			if ($char == '*' && $next == '/')
			{
				$current .= $next;
				$i++;
				$block_comment = FALSE;
			}

			continue;
		}

		if ($quote)
		{
			$current .= $char;

			if ($char == '\\' && $next !== '')
			{
				$current .= $next;
				$i++;
			}
			else if ($char == $quote)
			{
				$quote = NULL;
			}

			continue;
		}

		if (($char == '-' && $next == '-') || $char == '#')
		{
			$line_comment = TRUE;
			$current .= $char;
			continue;
		}

		if ($char == '/' && $next == '*')
		{
			$block_comment = TRUE;
			$current .= $char.$next;
			$i++;
			continue;
		}

		if ($char == '\'' || $char == '"' || $char == '`')
		{
			$quote = $char;
			$current .= $char;
			continue;
		}

		if ($char == ';')
		{
			$queries[] = $current;
			$current = '';
			continue;
		}

		$current .= $char;
	}

	if (trim($current) !== '')
	{
		$queries[] = $current;
	}

	return $queries;
}

function configure_site($mysqli, $config)
{
	$settings = [];

	if ($config['site_name'])
	{
		$settings['name'] = $config['site_name'];
	}

	if ($config['site_contact'])
	{
		$settings['contact'] = $config['site_contact'];
	}

	foreach ($settings as $name => $value)
	{
		$stmt = prepare($mysqli, 'UPDATE `settings` SET `value` = ? WHERE `name` = ? AND `site` = "" AND `lang` = ""');
		$stmt->bind_param('ss', $value, $name);
		execute($stmt);
		$stmt->close();
	}
}

function configure_admin($mysqli, $config)
{
	$password = password_hash($config['admin_pass'], PASSWORD_DEFAULT);

	$stmt = prepare($mysqli, 'UPDATE `user` SET `username` = ?, `password` = ?, `email` = ?, `admin` = "1", `deleted` = "0", `data` = "" WHERE `id` = 1');
	$stmt->bind_param('sss', $config['admin_user'], $password, $config['admin_email']);
	execute($stmt);
	$updated = $stmt->affected_rows;
	$stmt->close();

	if ($updated < 1)
	{
		$stmt = prepare($mysqli, 'INSERT INTO `user` (`id`, `username`, `password`, `email`, `registration_date`, `last_activity_date`, `admin`, `language`, `data`, `deleted`) VALUES (1, ?, ?, ?, CURRENT_TIMESTAMP, NULL, "1", NULL, "", "0")');
		$stmt->bind_param('sss', $config['admin_user'], $password, $config['admin_email']);
		execute($stmt);
		$stmt->close();
	}

	line('Configured admin account');
}

function write_htaccess($config)
{
	if ($config['no_htaccess'])
	{
		return;
	}

	$template = HIDDENCMS_ROOT.'/install/htaccess.txt';

	if (!is_file($template))
	{
		exit_with_error('Missing .htaccess template: '.$template);
	}

	$base = normalize_base($config['base']);
	write_file(HIDDENCMS_ROOT.'/.htaccess', str_replace('%BASE%', $base, file_get_contents($template)));
	line('Wrote .htaccess with RewriteBase '.$base);
}

function normalize_base($base)
{
	$base = trim($base);

	if ($base === '')
	{
		return '/';
	}

	if ($base[0] != '/')
	{
		$base = '/'.$base;
	}

	return rtrim($base, '/').'/';
}

function mark_install_complete()
{
	if (is_dir(HIDDENCMS_ROOT.'/install'))
	{
		if (is_file(HIDDENCMS_ROOT.'/install/db.txt'))
		{
			unlink(HIDDENCMS_ROOT.'/install/db.txt');
		}

		touch(HIDDENCMS_ROOT.'/install/installed.txt');
	}
}

function remove_directory($dir)
{
	if (!is_dir($dir))
	{
		return;
	}

	$items = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);

	foreach ($items as $item)
	{
		if ($item->isDir())
		{
			remove_path($item->getPathname(), TRUE);
		}
		else
		{
			remove_path($item->getPathname(), FALSE);
		}
	}

	unset($items);

	for ($i = 0; $i < 3 && is_dir($dir); $i++)
	{
		remove_path($dir, TRUE);

		if (is_dir($dir))
		{
			usleep(100000);
			clearstatcache(TRUE, $dir);
		}
	}

	if (is_dir($dir))
	{
		line('The install directory could not be fully removed. You can delete it manually.');
	}
	else
	{
		line('Removed install directory');
	}
}

function remove_path($path, $directory)
{
	if (!file_exists($path) && !is_dir($path))
	{
		return TRUE;
	}

	@chmod($path, 0777);

	if ($directory)
	{
		return @rmdir($path) || !is_dir($path);
	}

	return @unlink($path) || !file_exists($path);
}

function prompt($question, $default = NULL)
{
	$suffix = $default !== NULL && $default !== '' ? ' ['.$default.']' : '';
	fwrite(STDOUT, $question.$suffix.': ');
	$value = trim(fgets(STDIN));

	return $value === '' && $default !== NULL ? $default : $value;
}

function confirm($question, $default = FALSE)
{
	$answer = strtolower(prompt($question.' '.($default ? '[Y/n]' : '[y/N]'), $default ? 'y' : 'n'));

	return in_array($answer, ['y', 'yes', 'o', 'oui']);
}

function write_file($file, $content)
{
	$dir = dirname($file);

	if (!is_dir($dir))
	{
		mkdir($dir, 0777, TRUE);
	}

	if (file_put_contents($file, $content) === FALSE)
	{
		exit_with_error('Unable to write file: '.$file);
	}
}

function prepare($mysqli, $query)
{
	$stmt = $mysqli->prepare($query);

	if (!$stmt)
	{
		exit_with_error('Unable to prepare SQL query: '.$mysqli->error);
	}

	return $stmt;
}

function execute($stmt)
{
	if (!$stmt->execute())
	{
		exit_with_error('SQL query failed: '.$stmt->error);
	}
}

function escape_php($value)
{
	return str_replace(['\\', '\''], ['\\\\', '\\\''], $value);
}

function escape_identifier($value)
{
	return str_replace('`', '``', $value);
}

function generate_password()
{
	return substr(strtr(base64_encode(random_bytes(18)), '+/', '-_'), 0, 24);
}

function line($message)
{
	fwrite(STDOUT, $message."\n");
}

function exit_with_error($message)
{
	fwrite(STDERR, $message."\n");
	exit(1);
}

function print_usage()
{
	line('Usage: php install/cli.php [options]');
	line('');
	line('Database options:');
	line('  --db-host=localhost       Database host');
	line('  --db-port=3306            Database port');
	line('  --db-name=hiddencms       Database name');
	line('  --db-user=root            Database user');
	line('  --db-pass=secret          Database password');
	line('  --create-db               Create the database if it does not exist');
	line('');
	line('Admin options:');
	line('  --admin-user=admin        Admin username');
	line('  --admin-pass=secret       Admin password');
	line('  --admin-pass-env=NAME     Read admin password from an environment variable');
	line('  --admin-email=mail        Admin email');
	line('');
	line('Site options:');
	line('  --base=/hHiddenCMS/       Apache RewriteBase');
	line('  --site-name=HiddenCMS     Site name');
	line('  --site-contact=mail       Site contact email');
	line('');
	line('Behaviour options:');
	line('  --force                   Reinstall even if tables already exist');
	line('  --yes                     Do not ask for confirmation');
	line('  --no-htaccess             Do not write .htaccess');
	line('  --remove-installer        Delete install/ after installation');
	line('  --help                    Show this help');
}
