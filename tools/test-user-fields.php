<?php

// Explicit opt-in: all test records live in a temporary, isolated database.
if (PHP_SAPI !== 'cli' || !in_array('--isolated-database', $argv, TRUE))
{
	fwrite(STDERR, "Usage: php tools/test-user-fields.php --isolated-database\nRequires CREATE/DROP DATABASE privileges on a development server.\n");
	exit(1);
}
chdir(dirname(__DIR__));
define('HIDDENCMS_CLI', TRUE);
$_SERVER['REQUEST_METHOD'] = 'GET';
require 'index.php';

$module = HB()->module('user');
$db = HB()->db;
$original = $db->query('SELECT DATABASE()')->row();
$temporary = 'hb_test_users_'.bin2hex(random_bytes(6));
$quote = function($name){ return '`'.str_replace('`', '``', $name).'`'; };
$checks = 0;
$assert = function($condition, $message) use (&$checks){
	if (!$condition) throw new RuntimeException($message);
	$checks++;
	echo 'PASS '.$message.PHP_EOL;
};
$reject = function($callback, $message) use ($assert){
	try { $callback(); }
	catch (InvalidArgumentException $e) { $assert(TRUE, $message); return; }
	throw new RuntimeException('Expected rejection: '.$message);
};

$db->execute_checked('CREATE DATABASE '.$quote($temporary).' CHARACTER SET utf8mb4');
try
{
	foreach ($db->query('SHOW TABLES')->get() as $table)
	{
		$db->execute_checked('CREATE TABLE '.$quote($temporary).'.'.$quote($table).' LIKE '.$quote($original).'.'.$quote($table));
	}
	$db->execute_checked('INSERT INTO '.$quote($temporary).'.addon SELECT * FROM '.$quote($original).'.addon');
	$db->execute_script('USE '.$quote($temporary));
	// Prepared statements bind their database at preparation time.
	$statements = new ReflectionProperty($db->driver(), 'stmt');
	$statements->setAccessible(TRUE);
	$statements->setValue($db->driver(), []);
	$migration = require 'hiddencms/migrations/0.3.1.php';
	$migration->up($db);
	$migration->up($db);
	$fields = $module->model('fields');
	$original_profile_modes = [];
	foreach (privacy_profile_fields() as $field => $label)
	{
		$key = 'privacy_profile_'.$field;
		$original_profile_modes[$key] = HB()->config->$key ?? NULL;
		HB()->config->$key = 'public';
	}
	$assert($fields->identifier() === 'username', 'Default identifier is username; migration is idempotent');
	$text = $fields->save_definition(['name' => 'reference', 'label' => 'Référence', 'type' => 'text']);
	$ids = [];
	foreach (['select', 'radio', 'switch', 'checkbox'] as $type)
	{
		$ids[$type] = $fields->save_definition(['name' => $type, 'label' => ucfirst($type), 'type' => $type, 'options' => "a|Première\nb|Seconde"]);
	}
	$new = function($name, $reference) use ($module, $fields, $text, $ids){
		$user = $module->model2('user')->set('username', $name)->set('email', $name.'@example.test')->set('password', 'OnlyForTests!72');
		$user->set('custom_'.$text, $reference);
		foreach ($ids as $type => $id) $user->set('custom_'.$id, $type === 'checkbox' ? ['a', 'b'] : ($type === 'switch' ? ['1'] : 'a'));
		return $fields->save_user($user, TRUE);
	};
	$one = $new('first', 'Alpha');
	$two = $new('second', '');
	$assert($one->id && $two->id && $one->id !== $two->id, 'Create distinct users');
	$assert(!$one->admin && $one->password('OnlyForTests!72'), 'New account is not admin and password is hashed');
	$assert($fields->values($one->id)['custom_'.$ids['checkbox']] === ['a', 'b'], 'Multiple choices persist');
	$assert($fields->values($one->id)['custom_'.$ids['switch']] === ['1'], 'Switch persists');
	$assert((int)$fields->login_user('first')->id === (int)$one->id, 'Username login works');
	$assert(!$fields->login_user('first@example.test')(), 'Username mode rejects email');
	$fields->set_identifier('email');
	$assert((int)$fields->login_user('first@example.test')->id === (int)$one->id, 'Email login works');
	$assert(!$fields->login_user('first')(), 'Email mode rejects username');
	$reject(function() use ($fields, $text){ $fields->set_identifier('field:'.$text); }, 'Custom login rejects missing values');
	$assert($fields->identifier() === 'email', 'Failed switch preserves previous login setting');
	$two->set('custom_'.$text, 'alpha');
	$fields->save_user($two);
	$reject(function() use ($fields, $text){ $fields->set_identifier('field:'.$text); }, 'Custom login rejects case-insensitive duplicates');
	$two->set('custom_'.$text, 'Beta');
	$fields->save_user($two);
	$fields->set_identifier('field:'.$text);
	$assert((int)$fields->login_user('Alpha')->id === (int)$one->id, 'Custom login works');
	$assert(!$fields->login_user('first')() && !$fields->login_user('first@example.test')(), 'Custom mode has no username/email fallback');
	$reject(function() use ($fields, $text){ $fields->delete_definition($text); }, 'Active login field cannot be deleted');
	$reject(function() use ($fields, $ids){ $fields->set_identifier('field:'.$ids['select']); }, 'Only text fields can be login identifiers');
	$two->set('custom_'.$text, 'Alpha');
	$reject(function() use ($fields, $two){ $fields->save_user($two); }, 'Duplicate login value rejected on profile save');
	$assert($fields->values($two->id)['custom_'.$text] === 'Beta', 'Rejected save preserves profile values');
	$two->set('custom_'.$text, 'Beta')->set('custom_'.$ids['select'], 'invalid');
	$reject(function() use ($fields, $two){ $fields->save_user($two); }, 'Unknown choice rejected');
	$two->set('custom_'.$ids['select'], 'a')->set('custom_'.$ids['checkbox'], [['nested']]);
	$reject(function() use ($fields, $two){ $fields->save_user($two); }, 'Malformed checkbox values rejected');
	$two->set('custom_'.$ids['checkbox'], ['a']);
	$reject(function() use ($fields, $ids){ $fields->save_definition(['label' => 'Select', 'options' => 'b|Seconde'], $ids['select']); }, 'Used choice cannot be removed');
	$html = (string)$module->form2('username email password_required custom_fields', $one)->panel();
	$assert(strpos($html, 'custom_'.$text) !== FALSE && strpos($html, 'toggle') !== FALSE, 'Creation/profile form renders custom fields and switch');
	$admin = $module->controller('admin');
	$html = (string)$admin->fields(NULL);
	$assert(strpos($html, 'Nom technique') !== FALSE && strpos($html, 'Identifiant de connexion') !== FALSE, 'Admin field and login settings render');
	$html = (string)$admin->fields($fields->find($ids['select']));
	$assert(strpos($html, 'disabled') !== FALSE && strpos($html, 'Premi') !== FALSE, 'Existing field editor renders choices with immutable type');
	$html = (string)$admin->create();
	$assert(strpos($html, 'custom_'.$text) !== FALSE && strpos($html, 'password_confirm') !== FALSE, 'Admin user creation renders all fields');
	$theme_property = new ReflectionProperty(HB()->output, '_theme');
	$theme_property->setAccessible(TRUE);
	$previous_theme = $theme_property->getValue(HB()->output);
	$theme_property->setValue(HB()->output, HB()->theme('admin'));
	try
	{
		$edit_html = (string)$one->action('update');
		$dom = new DOMDocument();
		@$dom->loadHTML('<?xml encoding="utf-8" ?><div class="module-admin">'.$edit_html.'</div>');
		$xpath = new DOMXPath($dom);
		$tabs = $xpath->query('//*[@role="tab"]');
		$assert($tabs->length === 6, 'Admin user editor separates six sections into tabs');
		$assert($xpath->query('//*[@role="tabpanel" and contains(concat(" ",normalize-space(@class)," ")," active ")]')->length === 1, 'Only one user section is initially visible');
		foreach ($tabs as $tab)
		{
			$assert($xpath->query('//*[@role="tabpanel" and @id="'.$tab->getAttribute('aria-controls').'"]')->length === 1, 'User tab controls an existing panel');
		}
		$columns = $xpath->query('//*[@id="user-panel-account"]/div[contains(@class,"grid")]/div[contains(@class,"column")]');
		$assert($columns->length === 2, 'Account tab groups member settings and groups in two columns');
		foreach ($columns as $column)
		{
			$assert($xpath->query('./div[contains(@class,"card")]', $column)->length === 1, 'Account column contains only one section');
			$assert(strpos($column->getAttribute('class'), 'sixteen wide tablet') !== FALSE, 'User editor columns use full width on tablets');
		}
		$assert($xpath->query('//form//form')->length === 0, 'Tabbed user forms are not nested');
		$assert($xpath->query('//*[@id="user-panel-images"]//input[@type="file"]')->length === 2, 'Both image uploads remain available in Images');
		$assert($xpath->query('//*[@id="user-panel-profile"]//textarea')->length >= 1, 'Profile tab retains signature editor');
		$assert($xpath->query('//*[@id="user-panel-profile"]//select[@name="sex"]/option')->length === 3, 'Sex is a select with three choices');
		$assert($xpath->query('//*[@id="user-panel-profile"]//select[@name="sex"]/option[@value="unspecified" and @selected]')->length === 1, 'Unspecified sex is selected for an empty profile');
		$profile_rules = new ReflectionProperty('HB\\HiddenCMS\\Libraries\\Form2', '_rules');
		$profile_rules->setAccessible(TRUE);
		$profile_success = new ReflectionProperty('HB\\HiddenCMS\\Libraries\\Form2', '_success');
		$profile_success->setAccessible(TRUE);
		foreach (['female', 'male', 'unspecified'] as $sex)
		{
			$profile = $one->profile();
			$profile_form = $module->form2('profile', $profile);
			foreach ($profile_rules->getValue($profile_form) as $rule)
			{
				if ($rule->name() !== 'sex') continue;
				$data = [];
				$assert($rule->check(['sex' => $sex], $data), 'Sex choice validates: '.$sex);
				$profile->set('sex', $data['sex']);
				$profile_success->getValue($profile_form)[0]($profile);
				$profile->commit();
				$stored = $db->query('SELECT sex FROM user_profile WHERE id = '.(int)$one->id)->row();
				$assert($stored === ($sex === 'unspecified' ? NULL : $sex), 'Sex choice persists: '.$sex);
			}
		}
		$assert($xpath->query('//*[@id="user-panel-profile"]//input[@name="date_of_birth" and @data-calendar-type="date" and @data-calendar-locale="fr"]')->length === 1, 'Birth date uses the localized Fomantic calendar');
		$assert($xpath->query('//*[@id="user-panel-profile"]//input[contains(concat(" ", normalize-space(@class), " "), " date ")]')->length === 0, 'Birth date no longer initializes the Bootstrap calendar');
		$group_choices = $xpath->query('//ul[contains(@class,"user-group-list")]//input[@type="checkbox"]');
		$assert($group_choices->length > 0, 'Account renders group membership choices');
		foreach ($group_choices as $choice)
		{
			$assert($xpath->query('../label[@for="'.$choice->getAttribute('id').'"]', $choice)->length === 1, 'Group choice has an associated full-row label');
			$assert(strpos($choice->parentNode->getAttribute('class'), 'ui checkbox') !== FALSE, 'Group choice uses a Fomantic checkbox');
		}
		$previous_admin = HB()->user->admin;
		try
		{
			HB()->user->set('admin', TRUE);
			$list_html = (string)$admin->index($module->collection('user')->where('deleted', FALSE));
		}
		finally { HB()->user->set('admin', $previous_admin); }
		@$dom->loadHTML('<?xml encoding="utf-8" ?>'.$list_html);
		$xpath = new DOMXPath($dom);
		$actions = $xpath->query('//a[contains(@href,"admin/user/create") or contains(@href,"admin/user/fields")]');
		$assert($actions->length === 2, 'User list renders both management actions');
		foreach ($actions as $action)
		{
			$assert($xpath->query('ancestor::*[contains(concat(" ", normalize-space(@class), " "), " button ")]', $action)->length === 0, 'Management action is not nested inside another button');
		}
		$assert($actions->item(0)->parentNode->isSameNode($actions->item(1)->parentNode), 'Management actions are siblings in the panel footer');
		$group_form = $module->form()->add_rules('groups');
		$group_rules = new ReflectionProperty($group_form, '_rules');
		$group_rules->setAccessible(TRUE);
		$rules = $group_rules->getValue($group_form);
		$assert($rules['color']['type'] === 'select' && $rules['icon']['type'] === 'select', 'Group appearance uses selects without Bootstrap pickers');
		$assert($rules['color']['check']('success') === TRUE && $rules['color']['check']('unknown') !== TRUE, 'Group color validates allowed choices');
		$assert($rules['icon']['check']('fas fa-star') === TRUE && $rules['icon']['check']('invalid') !== TRUE, 'Group icon validates allowed choices');
		$assert($rules['icon']['check']('') === TRUE && $rules['color']['check']('') === TRUE, 'Group appearance can be cleared');
		$assert($rules['icon']['check'](['fas fa-star']) !== TRUE && $rules['color']['check'](['success']) !== TRUE, 'Group appearance rejects array values');
		$group_form->add_rules('groups', ['title' => 'Existing', 'color' => '#123456', 'icon' => 'fab fa-github']);
		$rules = $group_rules->getValue($group_form);
		$assert($rules['color']['check']('#123456') === TRUE && $rules['icon']['check']('fab fa-github') === TRUE, 'Existing custom colors and icons remain valid');
		@$dom->loadHTML('<?xml encoding="utf-8" ?>'.$group_form->display());
		$xpath = new DOMXPath($dom);
		$assert($xpath->query('//select[contains(@class,"dropdown")]/option[@value="#123456" and @selected]')->length === 1, 'Existing custom color is selected in Fomantic form');
		$assert($xpath->query('//select[contains(@class,"dropdown")]/option[@value="fab fa-github" and @selected]')->length === 1, 'Existing custom icon is selected in Fomantic form');
	}
	finally { $theme_property->setValue(HB()->output, $previous_theme); }
	$one->delete();
	$assert(!$fields->login_user('Alpha')(), 'Deleted account cannot log in');
	$two->set('custom_'.$text, 'Alpha');
	$fields->save_user($two);
	$assert((int)$fields->login_user('Alpha')->id === (int)$two->id, 'Deleted account releases its custom login value');
	$fields->set_identifier('username');
	$fields->delete_definition($text);
	$assert(!$fields->find($text) && !isset($fields->values($two->id)['custom_'.$text]), 'Field deletion removes stored values');
	$required = $fields->save_definition(['name' => 'required_text', 'label' => 'Required', 'type' => 'text', 'required' => ['1']]);
	$submitted = NULL;
	$make_form = function() use ($module, $fields, &$submitted){
		return $module->form2('username email password_required custom_fields', $module->model2('user'))
			->success(function($user) use ($fields, &$submitted){ $submitted = $fields->save_user($user, TRUE); });
	};
	$payload = ['username' => 'posted', 'email' => 'posted@example.test', 'password' => 'FormTest!72', 'password_confirm' => 'FormTest!72', 'custom_'.$required => '<b>R&D</b>', 'custom_'.$ids['select'] => 'a', 'custom_'.$ids['radio'] => 'b', 'custom_'.$ids['checkbox'] => ['a']];
	$form = $make_form();
	$_POST = $payload + ['_' => 'invalid-token'];
	$form->check();
	$assert($submitted === NULL, 'Creation rejects invalid CSRF token');
	$form = $make_form();
	$_POST = $payload + ['_' => $form->token()];
	unset($_POST['custom_'.$required]);
	$form->check();
	$assert($submitted === NULL, 'Creation form enforces required custom field');
	$form = $make_form();
	$_POST = $payload + ['_' => $form->token()];
	$_POST['custom_'.$ids['checkbox']] = [['invalid']];
	$form->check();
	$assert($submitted === NULL, 'Malformed multiple-choice submission produces a validation error');
	$form = $make_form();
	$_POST = $payload + ['_' => $form->token()];
	$form->check();
	$_POST = [];
	$assert($submitted && $submitted->password('FormTest!72'), 'Full creation form submission persists hashed account');
	$assert($fields->values($submitted->id)['custom_'.$required] === '<b>R&D</b>', 'Form entities are decoded once for storage');
	$assert($fields->values($submitted->id)['custom_'.$ids['switch']] === [], 'Unchecked switch is stored as off');
	$html = (string)$module->form2('custom_fields', $submitted)->panel();
	$assert(strpos($html, '<b>R&D</b>') === FALSE && strpos($html, '&lt;b&gt;R&amp;D&lt;/b&gt;') !== FALSE, 'Custom text is escaped on rendering');
	$assert(!$fields->login_user(['posted'])(), 'Malformed login identifier is rejected');
	$profile_modes = [];
	foreach (privacy_profile_fields() as $field => $label)
	{
		$key = 'privacy_profile_'.$field;
		$profile_modes[$key] = HB()->config->$key ?? NULL;
	}
	try
	{
		$profile = $two->profile();
		$seed = ['first_name' => 'PrivateFirst', 'last_name' => 'PrivateLast', 'date_of_birth' => '1990-06-15', 'sex' => 'female', 'country' => 'FR', 'location' => 'PrivateTown'];
		foreach ($seed as $field => $value) $profile->set($field, $value);
		$profile->commit();
		foreach (['public', 'private', 'disabled'] as $mode)
		{
			foreach ($profile_modes as $key => $previous) HB()->config->$key = $mode;
			$html = (string)$module->form2('profile', $profile)->panel();
			foreach ($seed as $field => $value)
			{
				$assert((strpos($html, 'name="'.$field.'"') !== FALSE) === ($mode !== 'disabled'), 'Profile field collection matches '.$mode.': '.$field);
				$assert((privacy_profile_value($profile, $field) !== NULL) === ($mode === 'public'), 'Profile field visibility matches '.$mode.': '.$field);
			}
			$public_html = (string)$two->view('profile');
			$mini_html = (string)$two->view('profile_mini');
			$assert((strpos($public_html, 'PrivateFirst') !== FALSE) === ($mode === 'public'), 'Public profile enforces name visibility: '.$mode);
			$assert((strpos($mini_html, 'PrivateLast') !== FALSE) === ($mode === 'public'), 'Profile preview enforces name visibility: '.$mode);
			$assert((strpos($public_html, 'PrivateTown') !== FALSE) === ($mode === 'public'), 'Public profile enforces location visibility: '.$mode);
			$assert((strpos($public_html, 'default_avatar_female') !== FALSE) === ($mode === 'public'), 'Default avatar does not reveal private sex: '.$mode);
			$assert(strpos($public_html, '15/06/1990') === FALSE, 'Public profile never exposes the full birth date tooltip');
		}
		$form = $module->form2('profile', $profile);
		$successes = new ReflectionProperty($form, '_success');
		$successes->setAccessible(TRUE);
		$successes->setValue($form, [function($profile){ $profile->commit(); }]);
		$_POST = ['_' => $form->token(), 'first_name' => 'Injected', 'last_name' => 'Injected', 'date_of_birth' => '01/01/2000', 'sex' => 'male', 'country' => 'US', 'location' => 'Injected', 'quote' => 'Allowed quote', 'signature' => ''];
		$form->check();
		$_POST = [];
		$stored = $db->select('first_name', 'last_name', 'date_of_birth', 'sex', 'country', 'location', 'quote')->from('user_profile')->where('id', $two->id)->row();
		foreach ($seed as $field => $value) $assert($stored[$field] === $value, 'Forged POST cannot update disabled field: '.$field);
		$assert($stored['quote'] === 'Allowed quote', 'Enabled profile fields still save while others are disabled');
		HB()->config->privacy_profile_sex = 'invalid';
		$assert(!privacy_profile_collects('sex') && privacy_profile_value($profile, 'sex') === NULL, 'Unknown privacy mode fails closed');
	}
	finally
	{
		$_POST = [];
		foreach ($profile_modes as $key => $value) HB()->config->$key = $value;
	}
	$privacy_keys = ['privacy_controller', 'privacy_contact', 'privacy_page'];
	$privacy_previous = [];
	foreach ($privacy_keys as $key)
	{
		$privacy_previous[$key] = HB()->config->$key ?? NULL;
		HB()->config->$key = '';
	}
	try
	{
		$assert(privacy_notice() === '', 'Privacy notice is absent until configured');
		$page = $db->insert('pages', ['name' => 'confidentialite-test', 'published' => TRUE, 'outline_id' => NULL]);
		$db->insert('pages_lang', ['page_id' => $page, 'lang' => 'fr', 'title' => 'Confidentialite test', 'subtitle' => '', 'content' => 'Test only']);
		$permission = $db->insert('access', ['module' => 'pages', 'action' => 'access_page', 'id' => $page]);
		$db->insert('access_details', ['access_id' => $permission, 'entity' => 'visitors', 'type' => 'group', 'authorized' => TRUE]);
		HB()->access->reload();
		HB()->config->privacy_page = (string)$page;
		$assert(strpos(privacy_policy_url(), 'confidentialite-test') !== FALSE, 'Privacy page resolves to a published internal page');
		HB()->config->privacy_contact = 'privacy@example.test';
		HB()->config->privacy_controller = '<script>alert(1)</script>';
		$notice = privacy_notice();
		$assert(strpos($notice, 'mailto:privacy@example.test') !== FALSE, 'Privacy notice exposes the configured contact');
		$assert(strpos($notice, '<script>') === FALSE && strpos($notice, '&lt;script&gt;') !== FALSE, 'Privacy controller name is escaped');
		HB()->config->privacy_contact = 'bad" onclick="alert(1)';
		$assert(strpos(privacy_notice(), 'mailto:') === FALSE, 'Invalid privacy email never becomes a link');
		$db->where('access_id', $permission)->update('access_details', ['authorized' => FALSE]);
		HB()->access->reload();
		$assert(privacy_policy_url() === '', 'Privacy page restricted to members is not linked to visitors');
		$db->where('access_id', $permission)->update('access_details', ['authorized' => TRUE]);
		HB()->access->reload();
		$db->where('page_id', $page)->update('pages', ['published' => FALSE]);
		$assert(privacy_policy_url() === '', 'Unpublished privacy page is not linked');
		$db->where('page_id', $page)->update('pages', ['published' => TRUE, 'name' => 'javascript:alert(1)']);
		$assert(privacy_policy_url() === '', 'Unsafe page path is not linked');
		$db->where('page_id', $page)->update('pages', ['name' => 'confidentialite-test']);
		HB()->config->privacy_contact = 'privacy@example.test';
		$registration = $module->controller('ajax')->register();
		$modal_body = new ReflectionProperty($registration, '_body');
		$modal_body->setAccessible(TRUE);
		$html = (string)$modal_body->getValue($registration);
		$assert(strpos($html, 'confidentialite-test') !== FALSE && strpos($html, 'mailto:privacy@example.test') !== FALSE, 'Registration includes privacy policy and contact without requiring consent');
		$previous_admin = HB()->user->admin;
		try
		{
			HB()->user->set('admin', TRUE);
			$html = (string)HB()->module('settings')->controller('admin')->privacy();
			$assert(strpos($html, 'name="privacy_page"') !== FALSE && strpos($html, 'name="privacy_contact"') !== FALSE, 'Privacy settings form renders page and contact controls');
			foreach (privacy_profile_fields() as $field => $label)
			{
				$assert(strpos($html, 'name="privacy_profile_'.$field.'"') !== FALSE, 'Privacy settings expose mode for '.$field);
				$assert(strpos(file_get_contents('install/DATABASE.sql'), "('privacy_profile_".$field."', '', '', 'disabled', 'string')") !== FALSE, 'New installations disable '.$field.' by default');
			}
		}
		finally { HB()->user->set('admin', $previous_admin); }
	}
	finally
	{
		foreach ($privacy_previous as $key => $value) HB()->config->$key = $value;
	}
	echo $checks." checks passed.\n";
}
finally
{
	foreach ($original_profile_modes ?? [] as $key => $value) HB()->config->$key = $value;
	if (isset($statements)) $statements->setValue($db->driver(), []);
	$db->execute_script('USE '.$quote($original));
	$db->execute_checked('DROP DATABASE '.$quote($temporary));
}
