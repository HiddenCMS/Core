<?php
$root = $argv[1] ?? dirname(__DIR__);
$language = $argv[2] ?? 'fr';
chdir($root);
define('HIDDENCMS_CLI', TRUE);
require 'index.php';
set_exception_handler(function($error){
    fwrite(STDERR, get_class($error).': '.$error->getMessage().PHP_EOL.$error->getTraceAsString().PHP_EOL);
    exit(1);
});
$lang = NULL;
foreach (HB()->config->langs as $candidate) {
    if ($candidate->info()->name === $language) { $lang = $candidate; break; }
}
if (!$lang) throw new RuntimeException('Test language is not enabled: '.$language);
HB()->config->lang = $lang;
HB()->config->langs = [$lang];
$expected = $language === 'fr' ? ['Sign in'=>'Se connecter', 'Create an account'=>'Créer un compte', 'Back to home'=>'Retour à l’accueil'] : ['Sign in'=>'Sign in', 'Create an account'=>'Create an account', 'Back to home'=>'Back to home'];
foreach ($expected as $source=>$translation) {
    $actual = (string)HB()->module('user')->lang($source);
    if ($actual !== $translation) throw new RuntimeException($source.': expected '.$translation.', got '.$actual);
    echo 'PASS '.$language.' '.$source.PHP_EOL;
}
$html = (string)HB()->widget('language')->output('index');
$label = $language === 'fr' ? 'Choisir la langue' : 'Choose language';
if (strpos($html, $label) === FALSE) throw new RuntimeException('Language widget label not translated');
echo 'PASS language widget accessible label'.PHP_EOL;
$catalogueChecks = [
    'Publish' => 'Publier', 'Published' => 'Publié', 'Unpublished' => 'Non publié',
    'Create a menu' => 'Créer un menu', 'Event saved' => 'Événement enregistré',
];
foreach ($catalogueChecks as $source => $french) {
    $expected = $language === 'fr' ? $french : $source;
    $actual = (string)HB()->module('pages')->lang($source);
    if ($actual !== $expected) throw new RuntimeException('French typography/publication: '.$source.' => '.$actual);
}
echo 'PASS publication labels and French accents'.PHP_EOL;
$dashboard = HB()->module('admin')->model('dashboard')->data();
$html = (string)HB()->module('admin')->view('dashboard', ['dashboard'=>$dashboard]);
$label = $language === 'fr' ? 'Contenus récents' : 'Recent content';
if (strpos($html, $label) === FALSE) throw new RuntimeException('Dashboard heading not translated');
echo 'PASS dashboard rendered in '.$language.PHP_EOL;
$overview = HB()->module('statistics')->model('overview');
$snapshot = $overview->snapshot(7);
foreach ($snapshot['series'] as $series) {
    if (!is_string($series['name'])) throw new RuntimeException('Chart labels must be serializable strings');
}
$traffic = HB()->module('statistics')->model('traffic')->report(7);
$html = (string)HB()->module('statistics')->view('overview', ['snapshot'=>$snapshot, 'traffic'=>$traffic, 'periods'=>[]]);
$label = $language === 'fr' ? 'Pages les plus vues' : 'Most viewed pages';
if (strpos($html, $label) === FALSE) throw new RuntimeException('Statistics heading not translated');
if (strpos($html, 'data-unavailable=') === FALSE) throw new RuntimeException('Missing translated chart fallback');
echo 'PASS statistics rendered in '.$language.PHP_EOL;
$csv = $overview->csv($snapshot);
$label = $language === 'fr' ? 'Nouveaux membres' : 'New members';
if (strpos($csv, $label) === FALSE) throw new RuntimeException('CSV labels not translated');
echo 'PASS CSV headings translated'.PHP_EOL;
try {
    HB()->module('settings')->model('smtp')->save(['smtp_enabled'=>'1', 'smtp_secure'=>'tls', 'smtp_port'=>'invalid']);
    throw new RuntimeException('Invalid SMTP configuration accepted');
} catch (InvalidArgumentException $error) {
    $expected = $language === 'fr' ? 'Configuration SMTP invalide.' : 'Invalid SMTP settings.';
    if ($error->getMessage() !== $expected) throw new RuntimeException('SMTP validation not translated: '.$error->getMessage());
}
echo 'PASS SMTP validation translated'.PHP_EOL;
$user = HB()->module('user');
$types = $user->model('fields')->types();
$expected = $language === 'fr' ? 'Cases à cocher' : 'Checkboxes';
if ($types['checkbox'] !== $expected) throw new RuntimeException('Custom field types not translated');
echo 'PASS custom field types translated'.PHP_EOL;
foreach (['register'=>'Join the site in a few moments.', 'lost_password_request'=>'Enter your email address to receive a reset link.'] as $method=>$source) {
    $html = (string)$user->controller('index')->$method();
    $expected = (string)$user->lang($source);
    if (strpos($html, $expected) === FALSE) throw new RuntimeException('Authentication page not translated: '.$method);
    echo 'PASS authentication page '.$method.' translated'.PHP_EOL;
}
$labels = privacy_profile_fields();
$expected = $language === 'fr' ? 'Prénom' : 'First name';
if ($labels['first_name'] !== $expected) throw new RuntimeException('Privacy profile labels not translated');
$services = privacy_services();
$expected = $language === 'fr' ? 'Vidéos YouTube' : 'YouTube videos';
if ($services['youtube']['title'] !== $expected) throw new RuntimeException('Cookie service titles not translated');
echo 'PASS profile privacy and cookie service labels translated'.PHP_EOL;
$model = HB()->module('outlines')->model2('outline');
$routes = new ReflectionMethod($model, 'reserved_outline_routes');
$routes->setAccessible(TRUE);
$titles = $routes->invoke($model, $user);
if (!isset($titles['login'], $titles['lost-password/*']) || !is_string($titles['login'])) throw new RuntimeException('Translated outline routes missing');
$expected = $language === 'fr' ? 'Se connecter' : 'Sign in';
if ($titles['login'] !== $expected) throw new RuntimeException('Outline route title not translated');
echo 'PASS translated outline route titles remain available'.PHP_EOL;
$files = HB()->module('files');
$html = (string)$files->picker_field('test_file');
$expected = $language === 'fr' ? 'Aucun fichier sélectionné' : 'No file selected';
if (strpos($html, $expected) === FALSE) throw new RuntimeException('File picker default label not translated');
$html = (string)$files->picker_directory_field('test_folder');
$expected = $language === 'fr' ? 'Aucun dossier sélectionné' : 'No folder selected';
if (strpos($html, $expected) === FALSE) throw new RuntimeException('Folder picker default label not translated');
$permissions = $files->permissions();
foreach (['directory'=>'Folder', 'file'=>'File'] as $scope=>$source) {
    foreach ($permissions[$scope]['get_all']() as $row) {
        $values = array_values($row);
        if (count($values) !== 2 || strpos($values[1], (string)$files->lang($source).' ') !== 0) throw new RuntimeException('File permission item label or identifier is invalid');
    }
}
echo 'PASS media picker defaults and permission item labels translated'.PHP_EOL;
$permissions = HB()->module('pages')->permissions();
$expected = $language === 'fr' ? 'Ajouter' : 'Add';
if ((string)$permissions['default']['access'][0]['access']['add_pages']['title'] !== $expected) throw new RuntimeException('Page permissions not translated');
echo 'PASS page permission labels translated'.PHP_EOL;
$menu = HB()->module('menu');
$permissions = $menu->permissions();
foreach ($permissions['link']['get_all']() as $row) {
    if (!isset($row['item_id'], $row['title']) || strpos($row['title'], (string)$menu->lang('Link').' ') !== 0) throw new RuntimeException('Menu permission item label or identifier is invalid');
}
foreach (['admin'=>'Dashboard', 'addons'=>'Themes & Addons', 'statistics'=>'Statistics'] as $name=>$source) {
    $module = HB()->module($name);
    if ((string)$module->info()->title !== (string)$module->lang($source)) throw new RuntimeException('Module metadata not translated: '.$name);
}
echo 'PASS menu permission items and module metadata translated'.PHP_EOL;
$contact = HB()->module('contact');
$html = (string)$contact->controller('index')->index();
$expected = $language === 'fr' ? 'Votre objet' : 'Your subject';
if (strpos($html, $expected) === FALSE) throw new RuntimeException('Contact subject label not translated');
$expected = $language === 'fr' ? 'Envoyer' : 'Send';
if (strpos($html, $expected) === FALSE) throw new RuntimeException('Contact submit label not translated');
echo 'PASS contact form translated without sending email'.PHP_EOL;
$choices = HB()->module('menu')->model2('menu')->get_parent_items(0);
$expected = $language === 'fr' ? 'Aucun (niveau racine)' : 'None (root level)';
if ($choices[''] !== $expected) throw new RuntimeException('Menu root choice not translated');
$source = 'Package %s has been installed. You can now enable its addon.';
$actual = (string)HB()->module('addons')->lang($source, 'example/package');
$expected = $language === 'fr' ? 'Le paquet example/package a été installé. Vous pouvez maintenant activer son addon.' : 'Package example/package has been installed. You can now enable its addon.';
if ($actual !== $expected) throw new RuntimeException('Addon installation notification placeholder not translated');
echo 'PASS menu root choice and addon notification translated'.PHP_EOL;
$savedFiles = $_FILES;
try {
    foreach ([1=>'The uploaded file exceeds upload_max_filesize in php.ini', 3=>'The file was only partially uploaded', 999=>'Unknown upload error'] as $code=>$source) {
        $_FILES['translation_upload_test'] = ['name'=>'test.png', 'error'=>$code, 'tmp_name'=>''];
        $field = HB()->form_file('translation_upload_test');
        $data = [];
        $field->check([], $data);
        $errors = array_map('strval', $field->errors());
        if (!in_array((string)HB()->lang($source), $errors, TRUE)) throw new RuntimeException('Upload error not translated: '.$code);
    }
} finally { $_FILES = $savedFiles; }
$field = HB()->form_iconpicker('translation_icon_test')->required();
$data = [];
$field->check(['translation_icon_test'=>'empty'], $data);
if (!in_array((string)HB()->lang('Please select an icon'), array_map('strval', $field->errors()), TRUE)) throw new RuntimeException('Icon picker validation not translated');
echo 'PASS upload and icon picker validation translated'.PHP_EOL;
foreach (['unconnected'=>'Sign-in required', 'unauthorized'=>'Access denied', 'unfound'=>'404 Not Found'] as $template=>$source) {
    $html = (string)HB()->view('errors/'.$template);
    if (strpos($html, (string)HB()->lang($source)) === FALSE) throw new RuntimeException('Error page not translated: '.$template);
}
echo 'PASS error page headings translated'.PHP_EOL;
$updater = HB()->core_updater;
$validate = new ReflectionMethod($updater, 'validate_release');
$validate->setAccessible(TRUE);
try {
    $validate->invoke($updater, ['name'=>'wrong/package', 'version'=>'999.0.0'], '999.0.0');
    throw new LogicException('Mismatched release was accepted');
} catch (RuntimeException $error) {
    if ($error->getMessage() !== (string)HB()->lang('The release does not match the announced manifest.')) throw $error;
}
$packages = HB()->addon_packages;
$validate = new ReflectionMethod($packages, 'validate_package');
$validate->setAccessible(TRUE);
try {
    $validate->invoke($packages, 'invalid package');
    throw new LogicException('Invalid Composer package name was accepted');
} catch (RuntimeException $error) {
    if ($error->getMessage() !== (string)HB()->lang('Invalid Composer package name.')) throw $error;
}
$feed = new class(HB()) extends HB\HiddenCMS\Libraries\Core_Updater {
    protected function request($url) { return '<feed xmlns="http://www.w3.org/2005/Atom"></feed>'; }
    public function test_empty_feed() { return $this->latest_release_from_feed(); }
};
try {
    $feed->test_empty_feed();
    throw new LogicException('Empty release feed was accepted');
} catch (RuntimeException $error) {
    if ($error->getCode() !== $feed::NO_STABLE_RELEASE || $error->getMessage() !== (string)HB()->lang('No stable HiddenCMS release has been published yet.')) throw $error;
}
echo 'PASS update and Composer errors translated without network or file changes'.PHP_EOL;
foreach (['dépendances Composer'=>'Composer dependencies', 'copie des fichiers'=>'File copy', 'synchronisation des addons'=>'Addon synchronization'] as $legacy=>$source) {
    $expected = (string)HB()->lang($source);
    if ($updater->stage_label($source) !== $expected || $updater->stage_label($legacy) !== $expected) throw new RuntimeException('Update stage translation or legacy compatibility failed');
}
if ($updater->stage_label('') !== (string)HB()->lang('Unknown step')) throw new RuntimeException('Missing stage fallback not translated');
if (class_exists(Composer\Semver\Semver::class)) {
    $validate = new ReflectionMethod($updater, 'validate_release');
    $validate->setAccessible(TRUE);
    try {
        $validate->invoke($updater, ['name'=>'hiddencms/core', 'version'=>'999.0.0', 'php'=>'>=999.0.0'], '999.0.0');
        throw new LogicException('Unsupported PHP constraint was accepted');
    } catch (RuntimeException $error) {
        if ($error->getMessage() !== (string)HB()->lang('This HiddenCMS version requires PHP %s.', '>=999.0.0')) throw $error;
    }
} else {
    echo 'SKIP PHP constraint validation: composer/semver is not installed'.PHP_EOL;
}
$html = (string)HB()->module('settings')->view('admin/updates', [
    'status'=>['core'=>['current'=>HIDDENCMS_VERSION, 'available'=>FALSE], 'addons'=>[], 'compatibility'=>[]],
    'last_failure'=>['failure'=>['stage'=>'dépendances Composer', 'message'=>'test diagnostic']],
    'backups'=>[]
]);
if (strpos($html, (string)HB()->lang('Composer dependencies')) === FALSE) throw new RuntimeException('Update page stage not translated');
echo 'PASS update stages and legacy metadata translated'.PHP_EOL;
$countries = get_countries();
if ($countries['de'] !== ($language === 'fr' ? 'Allemagne' : 'Germany')) throw new RuntimeException('Country names are not translated');
$html = (string)HB()->module('user')->form2('login')->panel();
foreach (['Password','Remember me','Sign in'] as $source) if (strpos($html, (string)HB()->lang($source)) === FALSE) throw new RuntimeException('Login field is not translated: '.$source);
if ($language === 'en' && preg_match('/Mot de passe|Se souvenir de moi|Se connecter/', $html)) throw new RuntimeException('French login source leaked into English');
$theme = HB()->theme('altitude');
if ($theme && (string)$theme->zone_title(0) !== ($language === 'fr' ? 'Barre haute' : 'Top bar')) throw new RuntimeException('Zone title is not translated');
echo 'PASS countries, login fields and theme zone titles translated'.PHP_EOL;
