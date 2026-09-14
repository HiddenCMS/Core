<?php if (!defined('HIDDENCMS_CMS')) exit;

ini_set('display_errors', FALSE);

if (!empty($_GET['test_gzip']))
{
	die;
}

if (!empty($_SERVER['REQUEST_URI']) && preg_match('_/check/install$_', $_SERVER['REQUEST_URI']))
{
	$base  = @$_SERVER['REDIRECT_CONTEXT'];
	$base2 = substr($_SERVER['SCRIPT_NAME'], 0, -9);

	if (strpos($_SERVER['REQUEST_URI'], $base.$base2) === 0)
	{
		$base .= $base2;
	}
	else
	{
		$base .= '/';
	}

	file_put_contents('.htaccess_tmp', str_replace('%BASE%', $base, file_get_contents('install/htaccess.txt')));

	exit('OK');
}

if (file_exists('.htaccess') && !file_exists('.htaccess_tmp'))
{
	$i = 0;

	do
	{
		$name = preg_replace('/_0$/', '', '.htaccess_old_'.$i++);
	}
	while (file_exists($name));

	rename('.htaccess', $name);
}

$default_lang = $lang = 'en';
$i18n = is_file(__DIR__.'/langs/'.$lang.'.php') ? include __DIR__.'/langs/'.$lang.'.php' : [];

if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) && preg_match_all('/([a-zA-Z-]+)(?:;q=([0-9.]+))?,?/', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches, PREG_SET_ORDER))
{
	$accepted = [];

	foreach ($matches as $match)
	{
		$accepted[$match[1]] = isset($match[2]) ? (float)$match[2] : 1;
	}

	arsort($accepted);

	foreach ($accepted as $name => $q)
	{
		if (file_exists('install/langs/'.$name.'.php'))
		{
			$lang = $name;
			$i18n = include 'install/langs/'.$lang.'.php';
			break;
		}
		else if ($default_lang == $name)
		{
			$lang = $name;
			break;
		}
	}
}

function lang($locale)
{
	global $default_lang, $lang, $i18n;

	$hash = hash('crc32b', $locale);
	if ($default_lang != $lang && array_key_exists($hash, $i18n))
	{
		$locale = $i18n[$hash];
	}

	return $locale;
}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
{
	require_once 'install/ajax.php';
	die;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang ?>">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, maximum-scale=1, initial-scale=1, user-scalable=0">
<meta content="IE=edge, chrome=1" http-equiv="X-UA-Compatible">
<meta name="theme-color" content="#2b373a">
<title><?php echo lang('Installation').' @HiddenCMS' ?></title>
<link rel="icon" href="dist/images/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="dist/css/bootstrap.min.css" type="text/css">
<link rel="stylesheet" href="install/install.css" type="text/css">
<link rel="stylesheet" href="dist/css/icons/fontawesome.min.css" type="text/css">
</head>
<body>
	<header class="installer-header">
		<label class="installer-theme-switch" for="installer-dark">
			<i class="fas fa-moon" aria-hidden="true"></i>
			<input id="installer-dark" type="checkbox" role="switch">
			<span><?php echo lang('Dark mode') ?></span>
		</label>
		<a class="logo" href="https://github.com/HiddenCMS/Core" target="_blank" rel="noopener"><img src="dist/images/logo/hiddencms.svg" alt="HiddenCMS" /></a>
		<nav class="installer-progress" aria-label="<?php echo lang('Installation steps') ?>">
			<ol>
				<li data-stage="check"><span>1</span><?php echo lang('System') ?></li>
				<li data-stage="db"><span>2</span><?php echo lang('Database') ?></li>
				<li data-stage="user"><span>3</span><?php echo lang('Administrator') ?></li>
				<li data-stage="done"><span>4</span><?php echo lang('Ready') ?></li>
			</ol>
		</nav>
	</header>
	<div class="installer-error alert alert-danger" role="alert" hidden data-message="<?php echo htmlspecialchars(lang('The request failed. Please try again. If the problem persists, check the server logs.'), ENT_QUOTES) ?>">
		<span></span>
		<button class="btn btn-outline-danger" type="button" data-action="retry"><?php echo lang('Try again') ?></button>
	</div>
	<div class="container">
		<?php if (!file_exists('install/db.txt')): ?>
			<section class="step check-init">
				<div class="row">
					<div class="col-12">
						<div class="heading">
							<a href="#" class="btn btn-action btn-success invisible float-right" data-action="next-step"><?php echo lang('Continue') ?> <i class="fa fa-angle-right" aria-hidden="true"></i></a>
							<h1><?php echo lang('Installation') ?></h1>
							<p class="lead"><?php echo lang('Welcome! Follow the steps to install your new HiddenCMS site') ?></p>
						</div>
					</div>
				</div>
				<div class="row first-check">
					<div class="col-12">
						<div class="legend">
							<div class="checking">
								<i class="fas fa-circle-notch fa-spin fa-3x float-left mr-3"></i> <?php echo lang('Before we start, we need to check the compatibility of your system.<br>Please wait a few moments...') ?>
							</div>
							<div class="errors d-none">
								<i class="fas fa-dizzy fa-3x float-left mr-3 text-danger"></i> <?php echo lang('<b>Oops...</b> It can\'t works!<br>Please resolve <span class="text-danger"><i class="fas fa-times"></i> blocking errors</span> below to continue the installation...') ?>
							</div>
						</div>
					</div>
				</div>
				<div class="row first-check-errors d-none">
					<div class="col-12">
						<ul class="list-group no-margin">
							<li class="list-group-item d-none">
								<div class="float-right">
									<ul class="list-inline no-margin">
									</ul>
								</div>
								{icon} {title}
							</li>
						</ul>
					</div>
				</div>
			</section>
			<section class="step" data-step="db">
				<div class="row">
					<div class="col-12">
						<div class="heading">
							<a href="#" class="btn btn-action btn-success invisible float-right" data-action="next-step"><?php echo lang('Continue') ?> <i class="fa fa-angle-right" aria-hidden="true"></i></a>
							<h1><?php echo lang('Database') ?></h1>
							<p class="lead"><?php echo lang('Connection to your database') ?></p>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-10 offset-md-1">
						<form autocomplete="off" novalidate>
							<div class="form-group row">
								<label for="language" class="col-sm-4 control-label"><?php echo lang('Primary language') ?> <em>*</em></label>
								<div class="col-sm-8">
									<select class="form-control" id="language" name="language" required>
										<option value="en" selected>English</option>
										<option value="fr">Fran&ccedil;ais</option>
									</select>
								</div>
							</div>
							<div class="form-group row">
								<label for="host" class="col-sm-4 control-label"><?php echo lang('Server') ?> <em>*</em></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="host" name="host" value="localhost" required>
									<small class="form-text text-muted">
										<?php echo lang('Corresponds to the address of your database. If you do not know it, ask for this information to your host') ?>
									</small>
								</div>
							</div>
							<div class="form-group row">
								<label for="dbname" class="col-sm-4 control-label"><?php echo lang('Database name') ?> <em>*</em></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="dbname" name="dbname" required>
									<small class="form-text text-muted">
										<?php echo lang('Name of the database where you want to install HiddenCMS') ?>
									</small>
								</div>
							</div>
							<div class="form-group row">
								<label for="user" class="col-sm-4 control-label"><?php echo lang('Username') ?> <em>*</em></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="user" name="user">
									<small class="form-text text-muted">
										<?php echo lang('Your username to access your database') ?>
									</small>
								</div>
							</div>
							<div class="form-group row">
								<label for="password" class="col-sm-4 control-label"><?php echo lang('Password') ?></label>
								<div class="col-sm-8">
									<input type="password" class="form-control" id="password" name="password">
									<small class="form-text text-muted">
										<?php echo lang('Your password to access your database') ?>
									</small>
								</div>
							</div>
							<div class="form-group row">
								<div class="offset-sm-4 col-sm-8">
									<i class="text-muted"><?php echo lang('* Required informations') ?></i>
								</div>
							</div>
							<div class="form-group">
								<div class="offset-sm-4 col-sm-8">
									<button type="submit" class="btn btn-info" data-loading-text="<?php echo lang('Checking...') ?>"><?php echo lang('Test connection') ?></button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</section>
			<section class="step" data-stage="db">
				<div class="text-center heading blinking">
					<h1><i class="fas fa-circle-notch fa-spin mb-5"></i> <?php echo lang('Installation in progress') ?></h1>
				</div>
			</section>
		<?php else: ?>
			<section class="step" data-step="user">
				<div class="row">
					<div class="col">
						<div class="heading">
							<h1><?php echo lang('Administrator') ?></h1>
							<p class="lead"><?php echo lang('Creation of your admin access') ?></p>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-10 col-md-offset-1 offset-md-1">
						<form novalidate>
							<div class="form-group row">
								<label for="username" class="col-sm-4 control-label"><?php echo lang('Username') ?> <em>*</em></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="username" name="username" required>
								</div>
							</div>
							<div class="form-group row">
								<label for="password" class="col-sm-4 control-label"><?php echo lang('Password') ?> <em>*</em></label>
								<div class="col-sm-8">
									<input type="password" class="form-control" id="password" name="password" required>
								</div>
							</div>
							<div class="form-group row">
								<label for="password2" class="col-sm-4 control-label"><?php echo lang('Confirm password') ?> <em>*</em></label>
								<div class="col-sm-8">
									<input type="password" class="form-control" id="password2" name="password2" required>
								</div>
							</div>
							<div class="form-group row">
								<label for="email" class="col-sm-4 control-label"><?php echo lang('E-mail address') ?> <em>*</em></label>
								<div class="col-sm-8">
									<input type="email" class="form-control" id="email" name="email" required>
								</div>
							</div>
							<div class="form-group row">
								<div class="offset-sm-4 col-sm-8">
									<i class="text-muted"><?php echo lang('* Required informations') ?></i>
								</div>
							</div>
							<div class="form-group">
								<div class="offset-sm-4 col-sm-8">
									<button type="submit" class="btn btn-info" data-loading-text="<?php echo lang('Saving in progress...') ?>"><?php echo lang('Save') ?></button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</section>
			<section class="step" data-stage="done">
				<div class="row">
					<div class="col-md-12">
						<div class="heading">
							<h1><?php echo lang('Congratulations!') ?></h1>
							<h2><?php echo lang('Your website is now ready to be configured!') ?></h2>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<a href="https://github.com/HiddenCMS/Core" class="btn btn-action btn-link" target="_blank"><?php echo lang('HiddenCMS project') ?><i class="fa fa-angle-right"></i></a>
						<a href="admin/settings" class="btn btn-action btn-info btn-finished"><?php echo lang('Configure my website') ?><i class="fa fa-angle-right"></i></a>
					</div>
				</div>
			</section>
		<?php endif ?>
	</div>
	<script type="text/javascript" src="dist/js/jquery-3.2.1.min.js"></script>
	<script type="text/javascript" src="dist/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="install/install.js"></script>
</body>
</html>
<?php die;
