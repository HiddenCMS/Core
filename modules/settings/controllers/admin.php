<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Settings\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Admin extends Controller_Module
{
	public function index()
	{
		$this	->subtitle($this->lang('General'))
				->icon('fas fa-cog');

		$pages = [];

		if (($pages_module = @HB()->module('pages')) && $pages_module->is_enabled())
		{
			foreach ($pages_module->model()->get_pages() as $page)
			{
				if ($page['published'])
				{
					$pages[$page['name']] = $page['title'];
				}
			}
		}

		array_natsort($pages);

		$this	->form()
				->add_rules([
					'name' => [
						'label'  => $this->lang('Site Title'),
						'value'  => $this->config->name,
						'rules'  => 'required'
					],
					'description' => [
						'label'  => $this->lang('Site description'),
						'value'  => $this->config->description,
						'rules'  => 'required'
					],
					'favicon' => [
						'label'  => $this->lang('Site favicon'),
						'value'  => $this->config->favicon,
						'type'   => 'file',
						'upload' => 'favicons',
						'info'   => $this->lang(' image (square, min. %dpx and max. %d MB)', 16, file_upload_max_size() / 1024 / 1024),
						'check'  => function($filename, $ext){
							if (!in_array($ext, ['gif', 'jpeg', 'jpg', 'png', 'ico']))
							{
								return $this->lang('Please choose an image file');
							}

							list($w, $h) = getimagesize($filename);

							if ($w != $h)
							{
								return $this->lang('The image must be square');
							}
							else if ($w < 16)
							{
								return $this->lang('The image must be at least %dpx', 16);
							}
						}
					],
					'contact' => [
						'label'  => $this->lang('Contact Email'),
						'value'  => $this->config->contact,
						'type'   => 'email',
						'rules'  => 'required'
					],
					'default_page' => [
						'label'  => $this->lang('Home Page'),
						'values' => $pages,
						'value'  => $this->config->default_page,
						'type'   => 'select',
						'rules'  => 'required'
					],
					'analytics' => [
						'label'       => '<a href="https://analytics.google.com" target="_blank">'.$this->lang('Google Analytics code').'</a>',
						'description' => (string)$this->lang('Format G-XXXXXXXXXX (GA4). Loaded only after visitor consent, outside administration. Legacy UA codes are no longer loaded.'),
						'value'       => $this->config->analytics,
						'check'       => function($code){
							if (!is_empty($code) && !preg_match('/^(?:G-[A-Z0-9]+|UA-\d+-\d+)$/D', $code))
							{
								return $this->lang('This code is invalid');
							}
						}
					],
					'humans_txt' => [
						'label'  => '<a href="http://humanstxt.org" target="_blank">humans.txt</a>',
						'type'   => 'textarea',
						'value'  => $this->config->humans_txt
					],
					'robots_txt' => [
						'label'  => '<a href="http://www.robotstxt.org" target="_blank">robots.txt</a>',
						'type'   => 'textarea',
						'value'  => $this->config->robots_txt
					]
				])
				->add_submit($this->lang('Save'))
				->display_required(FALSE);

		if ($this->form()->is_valid($post))
		{
			foreach ($post as $var => $value)
			{
				$this->config(''.$var, $value);
			}

			notify((string)$this->lang('General settings saved successfully'));

			refresh();
		}

		return $this->_layout(function($col){
			$col->append($this	->panel()
								->heading($this->lang('General'), 'fas fa-cog')
								->body($this->form()->display())
			);
		});
	}

	public function smtp()
	{
		if (!$this->user->admin) return $this->error->unauthorized();
		$this->subtitle((string)$this->lang('Email delivery'))->icon('fas fa-envelope');
		$model = $this->model('smtp');
		$values = $model->values();
		return $this->_layout(function($col) use ($model, $values){
			$form = $this->form2();
			$form->rule($this->form_select('smtp_enabled')->title((string)$this->lang('Delivery method'))->data(['0' => (string)$this->lang('PHP mail'), '1' => 'SMTP'])->value((string)$values['enabled'])->check(function($post){
				if (!in_array($post['smtp_enabled'] ?? '', ['0', '1'], TRUE)) return (string)$this->lang('Invalid delivery method');
			}));
			$form->rule($this->form_text('smtp_host')->title((string)$this->lang('SMTP server'))->value($values['host'])->size('col-8')->check(function($post){
				$host = $post['smtp_host'] ?? '';
				if (($post['smtp_enabled'] ?? '') === '1' && $host === '') return (string)$this->lang('Enter the SMTP server.');
				if ($host !== '' && !filter_var($host, FILTER_VALIDATE_IP) && !filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) return (string)$this->lang('Invalid server: use a domain name or IP address.');
			}));
			$form->rule($this->form_number('smtp_port')->title('Port')->value($values['port'])->size('col-4')->check(function($post){
				if (filter_var($post['smtp_port'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]) === FALSE) return (string)$this->lang('The port must be between 1 and 65535.');
			}));
			$form->rule($this->form_select('smtp_secure')->title((string)$this->lang('Encryption'))->data(['tls' => (string)$this->lang('STARTTLS (usually 587)'), 'ssl' => (string)$this->lang('Implicit TLS (usually 465)'), '' => (string)$this->lang('None')])->value($values['secure'])->check(function($post){
				if (!in_array($post['smtp_secure'] ?? NULL, ['', 'tls', 'ssl'], TRUE)) return (string)$this->lang('Invalid encryption method');
			}));
			$form->rule($this->form_text('smtp_username')->title((string)$this->lang('Username'))->value($values['username'])->size('col-6'));
			$password = $this->form_password('smtp_password')->title((string)$this->lang('New password'))->placeholder((string)$this->lang('Leave blank to keep the password'))->size('col-6');
			$password->check(function($post) use ($password){
				$password->value('', TRUE);
				if (!is_string($post['smtp_password'] ?? '')) return (string)$this->lang('Invalid password');
			});
			$form->rule($password);
			$form->rule($this->form_select('smtp_clear_password')->title((string)$this->lang('Saved password'))->data(['0' => (string)$this->lang('Keep'), '1' => (string)$this->lang('Delete')])->value('0'));
			$form->legend((string)$this->lang('Sender'));
			$form->rule($this->form_email('smtp_from')->title((string)$this->lang('Email address'))->value($values['from'])->placeholder($this->config->contact)->size('col-6'));
			$form->rule($this->form_text('smtp_name')->title('Name')->value($values['name'])->placeholder($this->config->name)->size('col-6'));
			$col->append($form->success(function($data, $form) use ($model){
				try { $model->save($data); notify((string)$this->lang('Email settings saved')); refresh(); }
				catch (\Throwable $error) { $form->error((string)$this->lang('Unable to save the SMTP settings.')); error_log('[SMTP] Configuration could not be saved.'); }
			})->submit((string)$this->lang('Save'))->panel()->title((string)$this->lang('Email settings'), 'fas fa-envelope'));
			$test = $this->form2()->rule($this->form_email('smtp_test_recipient')->title((string)$this->lang('Recipient'))->required()->value($this->user->email));
			$col->append($test->success(function($data, $form){
				try {
					$sent = $this->email->to(utf8_html_entity_decode($data['smtp_test_recipient']))->subject((string)$this->lang('HiddenCMS test email'))->message('default', ['content' => (string)$this->lang('This message confirms that email delivery is working on your site.')])->send();
					if ($sent) notify((string)$this->lang('Message accepted by the mail server. Check your inbox and spam folder.'));
					else $form->error((string)$this->lang('Sending failed. Check the server, port, encryption and credentials.'));
				} catch (\Throwable $error) { $form->error((string)$this->lang('Sending failed. Check the SMTP settings.')); error_log('[SMTP] Test email failed.'); }
			})->submit((string)$this->lang('Send a test email'))->panel()->title((string)$this->lang('Test the saved settings'), 'fas fa-paper-plane'));
		});
	}

	public function registration()
	{
		$this	->subtitle('Inscriptions')
				->icon('fas fa-sign-in-alt fa-rotate-90');

		$users = $this->db	->select('id as user_id', 'username')
							->from('user')
							->where('deleted', FALSE)
							->order_by('username')
							->get();

		$list_users = [];

		foreach ($users as $user)
		{
			$list_users[$user['user_id']] = $user['username'];
		}

		array_natsort($list_users);

		$this	->form()
				->add_rules([
					[
						'label'   => (string)$this->lang('Sign up'),
						'type'    => 'legend'
					],
					'registration_status' => [
						'label'   => (string)$this->lang('Status'),
						'type'    => 'radio',
						'value'   => (int)$this->config->registration_status,
						'values'  => [(string)$this->lang('Closed registrations'), (string)$this->lang('Open registrations')]
					],
					/*'registration_validation' => [
						'label'   => 'Validation',
						'type'    => 'radio',
						'value'   => (int)$this->config->registration_validation,
						'values'  => ['Automatique', 'Confirmation par e-mail']
					],*/
					'registration_charte' => [
						'label'   => (string)$this->lang('Rules'),
						'value'   => $this->config->registration_charte,
						'type'    => 'editor'
					],
					[
						'label'   => (string)$this->lang('Welcome message'),
						'type'    => 'legend'
					],
					'welcome' => [
						'type'    => 'checkbox',
						'checked' => ['on' => $this->config->welcome],
						'values'  => ['on' => (string)$this->lang('Send a private message to new users')]
					],
					'welcome_user_id' => [
						'label'   => (string)$this->lang('Message author'),
						'values'  => $list_users,
						'value'   => $this->config->welcome_user_id,
						'type'    => 'select',
						'size'    => 'col-5'
					],
					'welcome_title' => [
						'label'   => (string)$this->lang('Message title'),
						'value'   => $this->config->welcome_title,
						'type'    => 'text'
					],
					'welcome_content' => [
						'label'   => (string)$this->lang('Welcome message'),
						'value'   => $this->config->welcome_content,
						'type'    => 'editor',
						'description' => (string)$this->lang('Use [pseudo] to automatically insert the new member\'s username in the message')
					]
				])
				->add_submit($this->lang('Save'))
				->display_required(FALSE);

		if ($this->form()->is_valid($post))
		{
			foreach ($post as $var => $value)
			{
				if ($var == 'welcome')
				{
					$value = in_array('on', $value);
				}

				$this->config(''.$var, $value);
			}

			notify((string)$this->lang('Registration settings saved successfully'));

			refresh();
		}

		return $this->_layout(function($col){
			$col->append($this	->panel()
								->heading('Inscriptions', 'fas fa-sign-in-alt fa-rotate-90')
								->body($this->form()->display())
			);
		});
	}

	public function team()
	{
		$this	->subtitle((string)$this->lang('Site identity'))
				->icon('fas fa-id-card');

		$this	->form()
				->add_rules([
					'team_name' => [
						'label'       => 'Nom de la structure',
						'value'       => $this->config->team_name,
						'type'        => 'text'
					],
					'team_logo' => [
						'label'       => 'Logo',
						'value'       => $this->config->team_logo,
						'type'        => 'file',
						'upload'      => 'logos',
						'info'        => ' d\'image (max. '.(file_upload_max_size() / 1024 / 1024).' Mo)',
						'check'       => function($filename, $ext){
							if (!in_array($ext, ['gif', 'jpeg', 'jpg', 'png']))
							{
								return $this->lang('Please choose an image file');
							}
						},
						'description' => (string)$this->lang('This logo can be used by the theme and widgets instead of the site title.')
					],
					'login_logo' => [
						'label' => 'Afficher le logo au-dessus de la connexion',
						'value' => (string)($this->config->login_logo ?? '0'),
						'type' => 'select',
						'values' => ['0' => (string)$this->lang('No'), '1' => (string)$this->lang('Yes')],
						'description' => 'Utilise le logo du site renseigne ci-dessus.'
					],
					'team_type' => [
						'label'       => (string)$this->lang('Organization type'),
						'value'       => $this->config->team_type,
						'type'        => 'text',
						'size'        => 'col-4',
						'description' => '<b>Exemples :</b> association, entreprise, marque, collectif ou projet.'
					],
					'team_creation' => [
						'label'       => (string)$this->lang('Creation date'),
						'value'       => $this->config->team_creation,
						'type'        => 'date',
						'size'        => 'col-4'
					],
					'team_biographie' => [
						'label'       => (string)$this->lang('Introduction'),
						'value'       => $this->config->team_biographie,
						'type'        => 'textarea'
					]
				])
				->add_submit($this->lang('Save'))
				->display_required(FALSE);

		if ($this->form()->is_valid($post))
		{
			foreach ($post as $var => $value)
			{
				$this->config(''.$var, $value);
			}

			notify((string)$this->lang('Information saved successfully'));

			refresh();
		}

		return $this->_layout(function($col){
			$col->append($this	->panel()
								->heading((string)$this->lang('Site identity'), 'fas fa-id-card')
								->body($this->form()->display())
			);
		});
	}

	public function socials()
	{
		$this	->subtitle((string)$this->lang('Social networks'))
				->icon('fas fa-globe');

		$this	->form()
				->add_rules([
					'social_facebook' => [
						'label' => 'Facebook',
						'icon'  => 'fab fa-facebook-f',
						'value' => $this->config->social_facebook,
						'type'  => 'url'
					],
					'social_twitter' => [
						'label' => 'Twitter',
						'icon'  => 'fab fa-twitter',
						'value' => $this->config->social_twitter,
						'type'  => 'url'
					],
					'social_google' => [
						'label' => 'Google+',
						'icon'  => 'fab fa-google-plus-g',
						'value' => $this->config->social_google,
						'type'  => 'url'
					],
					'social_steam' => [
						'label' => 'Steam',
						'icon'  => 'fab fa-steam',
						'value' => $this->config->social_steam,
						'type'  => 'url'
					],
					'social_twitch' => [
						'label' => 'Twitch',
						'icon'  => 'fab fa-twitch',
						'value' => $this->config->social_twitch,
						'type'  => 'url'
					],
					'social_dribble' => [
						'label' => 'Dribbble',
						'icon'  => 'fab fa-dribbble',
						'value' => $this->config->social_dribble,
						'type'  => 'url'
					],
					'social_behance' => [
						'label' => 'Behance',
						'icon'  => 'fab fa-behance',
						'value' => $this->config->social_behance,
						'type'  => 'url'
					],
					'social_deviantart' => [
						'label' => 'DeviantArt',
						'icon'  => 'fab fa-deviantart',
						'value' => $this->config->social_deviantart,
						'type'  => 'url'
					],
					'social_flickr' => [
						'label' => 'Flickr',
						'icon'  => 'fab fa-flickr',
						'value' => $this->config->social_flickr,
						'type'  => 'url'
					],
					'social_github' => [
						'label' => 'GitHub',
						'icon'  => 'fab fa-github',
						'value' => $this->config->social_github,
						'type'  => 'url'
					],
					'social_instagram' => [
						'label' => 'Instagram',
						'icon'  => 'fab fa-instagram',
						'value' => $this->config->social_instagram,
						'type'  => 'url'
					],
					'social_youtube' => [
						'label' => 'YouTube',
						'icon'  => 'fab fa-youtube',
						'value' => $this->config->social_youtube,
						'type'  => 'url'
					]
				])
				->add_submit($this->lang('Save'))
				->display_required(FALSE);

		if ($this->form()->is_valid($post))
		{
			foreach ($post as $var => $value)
			{
				$this->config(''.$var, $value);
			}

			notify((string)$this->lang('Social network settings saved successfully'));

			refresh();
		}

		return $this->_layout(function($col){
			$col->append($this	->panel()
								->heading((string)$this->lang('Social networks'), 'fas fa-globe')
								->body($this->form()->display())
			);
		});
	}

	public function captcha()
	{
		$this	->subtitle('Protection anti-robots')
				->icon('fas fa-shield-alt');

		$this	->form()
				->add_rules([
					'captcha_public_key' => [
						'label' => (string)$this->lang('reCAPTCHA v3 site key'),
						'value' => $this->config->captcha_public_key,
						'type'  => 'text'
					],
					'captcha_private_key' => [
						'label' => (string)$this->lang('reCAPTCHA v3 secret key'),
						'value' => $this->config->captcha_private_key,
						'type'  => 'text'
					],
					'captcha_score_threshold' => [
						'label'       => (string)$this->lang('Minimum score'),
						'value'       => isset($this->config->captcha_score_threshold) ? $this->config->captcha_score_threshold : '0.5',
						'type'        => 'number',
						'min'         => 0,
						'max'         => 1,
						'step'        => 0.1,
						'description' => (string)$this->lang('Value between 0 and 1. The recommended initial threshold is 0.5.'),
						'check'       => function($value){
							$value = str_replace(',', '.', $value);
							return is_numeric($value) && (float)$value >= 0 && (float)$value <= 1 ? TRUE : (string)$this->lang('The threshold must be between 0 and 1');
						}
					]
				])
				->add_submit($this->lang('Save'))
				->display_required(FALSE);

		if ($this->form()->is_valid($post))
		{
			$post['captcha_score_threshold'] = str_replace(',', '.', $post['captcha_score_threshold']);

			foreach ($post as $var => $value)
			{
				$this->config(''.$var, $value);
			}

			notify((string)$this->lang('Google reCAPTCHA settings saved successfully'));

			refresh();
		}

		return $this->_layout(function($col){
			$col->append($this	->panel()
								->heading('Google reCAPTCHA', 'fas fa-shield-alt')
								->body('<div class="ui info message"><div class="header">reCAPTCHA v3</div><p>'.$this->lang('Use a pair of reCAPTCHA v3 keys. Verification is invisible and assigns a confidence score to each protected submission.').'</p><p><a href="https://www.google.com/recaptcha/admin/create" target="_blank" rel="noopener noreferrer">'.$this->lang('Configure Google reCAPTCHA').'</a></p></div>'.$this->form()->display())
			);
		});
	}

	public function maintenance()
	{
		$this	->subtitle($this->lang('Maintenance'))
				->icon('fas fa-power-off')
				->css('admin/maintenance')
				->js('admin/maintenance');

		$form_opening = $this->form()
			->add_rules([
				'opening' => [
					'type'  => 'datetime',
					'value' => $this->config->maintenance_opening
				]
			])
			->fast_mode()
			->add_submit($this->lang('Save'))
			->save();

		$position = preg_split('/\s+/', trim((string)$this->config->maintenance_background_position), -1, PREG_SPLIT_NO_EMPTY);
		$positionX = 'center';
		$positionY = 'top';

		if (count($position) > 1)
		{
			if (in_array($position[0], ['left', 'center', 'right'], TRUE)) $positionX = $position[0];
			if (in_array($position[1], ['top', 'center', 'bottom'], TRUE)) $positionY = $position[1];
		}
		else if (isset($position[0]))
		{
			if (in_array($position[0], ['left', 'right'], TRUE))
			{
				$positionX = $position[0];
				$positionY = 'center';
			}
			else if (in_array($position[0], ['top', 'bottom'], TRUE))
			{
				$positionY = $position[0];
			}
			else if ($position[0] == 'center')
			{
				$positionY = 'center';
			}
		}

		$maintenance_groups = [];
		foreach ($this->groups() as $group_id => $group)
		{
			if (!in_array($group_id, ['admins', 'visitors'], TRUE))
			{
				$maintenance_groups[$group_id] = $group['title'];
			}
		}

		$allowed_groups = array_values(array_filter((array)$this->config->maintenance_allowed_groups));

		$form_maintenance = $this->form()
			->add_rules([
				'title' => [
					'label' => $this->lang('Title'),
					'type'  => 'text',
					'value' => $this->config->maintenance_title
				],
				'content' => [
					'label' => $this->lang('Content'),
					'type'  => 'textarea',
					'value' => $this->config->maintenance_content
				],
				'logo' => [
					'label'  => $this->lang('Logo'),
					'value'  => $this->config->maintenance_logo,
					'type'   => 'file',
					'upload' => 'maintenance',
					'info'   => $this->lang(' image (max. %d MB)', file_upload_max_size() / 1024 / 1024),
					'check'  => function($filename, $ext){
						if (!in_array($ext, ['gif', 'jpeg', 'jpg', 'png']))
						{
							return $this->lang('Please choose an image file');
						}
					}
				],
				'background' => [
					'label'  => $this->lang('Background image'),
					'value'  => $this->config->maintenance_background,
					'type'   => 'file',
					'upload' => 'maintenance',
					'info'   => $this->lang(' image (max. %d MB)', file_upload_max_size() / 1024 / 1024),
					'check'  => function($filename, $ext){
						if (!in_array($ext, ['gif', 'jpeg', 'jpg', 'png']))
						{
							return $this->lang('Please choose an image file');
						}
					}
				],
				'repeat' => [
					'label'  => $this->lang('Repeat image'),
					'value'  => $this->config->maintenance_background_repeat ?: 'no-repeat',
					'values' => [
						'no-repeat' => $this->lang('No'),
						'repeat-x'  => $this->lang('Horizontally'),
						'repeat-y'  => $this->lang('Vertically'),
						'repeat'    => $this->lang('Both')
					],
					'type'   => 'select'
				],
				'positionX' => [
					'label'  => $this->lang('Horizontal position'),
					'value'  => $positionX,
					'values' => [
						'left'   => $this->lang('Left'),
						'center' => $this->lang('Centered'),
						'right'  => $this->lang('Right')
					],
					'type'   => 'select'
				],
				'positionY' => [
					'label'  => $this->lang('Vertical position'),
					'value'  => $positionY,
					'values' => [
						'top'    => $this->lang('Top'),
						'center' => $this->lang('Middle'),
						'bottom' => $this->lang('Bottom')
					],
					'type'   => 'select'
				],
				'background_color' => [
					'label' => $this->lang('Background color'),
					'value' => $this->config->maintenance_background_color ?: '#343a40',
					'type'  => 'colorpicker',
					'size'  => 'col-4'
				],
				'text_color' => [
					'label' => $this->lang('Text color'),
					'value' => $this->config->maintenance_text_color ?: '#fff',
					'type'  => 'colorpicker',
					'size'  => 'col-4'
				],
				'allowed_groups' => [
					'label'       => $this->lang('Groups allowed during maintenance'),
					'description' => $this->lang('Members of these groups can access the website while maintenance mode is enabled. Administrators always retain access.'),
					'type'        => 'select',
					'multiple'    => TRUE,
					'values'      => $maintenance_groups,
					'checked'     => array_fill_keys($allowed_groups, TRUE)
				]
			])
			->add_submit($this->lang('Save'))
			->save();

		if ($form_opening->is_valid($post))
		{
			$this->config('maintenance_opening', $post['opening']);
			refresh();
		}
		else if ($form_maintenance->is_valid($post))
		{
			$selected_groups = array_values(array_intersect(array_keys($maintenance_groups), $post['allowed_groups']));

			$this	->config('maintenance_title',               $post['title'])
					->config('maintenance_content',             $post['content'])
					->config('maintenance_logo',                $post['logo'], 'int')
					->config('maintenance_background',          $post['background'], 'int')
					->config('maintenance_background_repeat',   $post['repeat'])
					->config('maintenance_background_position', trim($post['positionX'].' '.$post['positionY']))
					->config('maintenance_background_color',    trim($post['background_color']))
					->config('maintenance_text_color',          trim($post['text_color']))
					->config('maintenance_allowed_groups',      implode('|', $selected_groups), 'list');

			$this->module('tools')->api()->scss('reload', ['./modules/settings/css/sass/maintenance.scss']);

			refresh();
		}

		return $this->_layout(function($right, $left) use ($form_maintenance, $form_opening){
			$right->append($this->panel()
								->heading($this->lang('Customizing the maintenance page'), 'fas fa-paint-brush')
								->body($form_maintenance->display())
			);

			$left	->append($this	->panel()
									->heading($this->lang('Website status'), 'fas fa-power-off')
									->body($this->view('admin/maintenance'))
					)
					->append($this	->panel()
									->heading($this->lang('Planned opening'), 'far fa-clock')
									->body($form_opening->display())
					);
		});
	}

	public function updates()
	{
		$this	->title('Updates')
				->icon('fas fa-cloud-download-alt')
				->css('admin/updates');

		$refresh = (bool)$this->input->get->get('refresh');
		$status = $refresh ? $this->core_updater->status(TRUE) : $this->core_updater->status_snapshot();

		$this->add_action($this->button((string)$this->lang('Search'), 'fas fa-sync', 'primary', 'admin/settings/updates?refresh=1'));

		return $this->view('admin/updates', [
			'status'       => $status,
			'backups'      => $this->core_updater->backups(),
			'last_failure' => $this->core_updater->last_failure()
		]);
	}

	public function privacy()
	{
		if (!$this->user->admin) return $this->error->unauthorized();
		$this->subtitle((string)$this->lang('Privacy'))->icon('fas fa-user-shield');
		$pages = ['0' => (string)$this->lang('No page selected')];
		foreach (privacy_pages() as $id => $page) $pages[$id] = $page['title'];
		$retention = $this->model('retention');
		return $this->_layout(function($col) use ($pages, $retention){
			$form = $this->form2()
				->info($this->html()->attr('class', 'ui warning message')->content((string)$this->lang('The policy must reflect the actual processing on this site. The manager blocks Google Analytics and embedded YouTube videos before consent. Other services require explicit integration. reCAPTCHA still requires separate review: these settings do not constitute a compliance assessment.')))
				->rule($this->form_text('privacy_controller')->title((string)$this->lang('Data controller'))->value($this->config->privacy_controller ?? ''))
				->rule($this->form_email('privacy_contact')->title((string)$this->lang('Personal data contact email'))->value($this->config->privacy_contact ?? ''))
				->rule($this->form_select('privacy_page')->title('Privacy policy')->data($pages)->search(0)->value((string)(($this->config->privacy_page ?? '') ?: '0'))
					->check(function($post) use ($pages){
						if (!is_scalar($post['privacy_page'] ?? NULL) || !array_key_exists($post['privacy_page'], $pages)) return (string)$this->lang('Select a published page accessible to visitors.');
					}))
				->rule($this->form_select('privacy_erasure_delay')->title((string)$this->lang('Anonymization delay'))->data([
					'7' => (string)$this->lang('7 days'), '14' => (string)$this->lang('14 days'), '30' => (string)$this->lang('30 days')
				])->value((string)$this->module('user')->model('privacy')->erasure_delay())
					->check(function($post){
						if (!in_array((int)($post['privacy_erasure_delay'] ?? 0), \HB\Modules\User\Models\Privacy::ERASURE_DELAYS, TRUE)) return (string)$this->lang('Invalid delay');
					}));
			$form->rule($this->form_select('statistics_enabled')->title((string)$this->lang('Local site statistics'))->data(['1' => (string)$this->lang('Enabled after visitor consent'), '0' => (string)$this->lang('Disabled')])->value($this->config->statistics_enabled ?? '1')->check(function($post){
				if (!in_array($post['statistics_enabled'] ?? NULL, ['0', '1'], TRUE)) return (string)$this->lang('Invalid choice');
			}));
			$form->legend((string)$this->lang('Profile fields'));
			foreach (privacy_profile_fields() as $field => $label)
			{
				$key = 'privacy_profile_'.$field;
				$form->rule($this->form_select($key)->title($label)->size('col-6')
					->data(['disabled' => (string)$this->lang('Disabled option'), 'private' => (string)$this->lang('Private'), 'public' => 'Public'])
					->value(privacy_profile_mode($field))
					->check(function($post) use ($key){
						if (!in_array($post[$key] ?? NULL, ['disabled', 'private', 'public'], TRUE)) return (string)$this->lang('Invalid choice');
					}));
			}
			$form->legend((string)$this->lang('Retention periods'));
			$form->info('<div class="ui info message"><p>'.$this->lang('A disabled value does not trigger automatic deletion. Inactive accounts are only flagged for review and are never deleted by this purge.').'</p></div>');
			$retention_titles = [
				'privacy_retention_connection_history' => (string)$this->lang('Connection history'),
				'privacy_retention_sessions'           => (string)$this->lang('Persistent sessions'),
				'privacy_retention_db_logs'            => (string)$this->lang('Change log'),
				'privacy_retention_erasure_reports'    => (string)$this->lang('Completed erasure reports'),
				'privacy_retention_backups'            => (string)$this->lang('Update backups'),
				'privacy_retention_log_files'          => (string)$this->lang('Old log files'),
				'privacy_retention_inactive_accounts'  => (string)$this->lang('Inactive account audit')
			];
			$retention_values = $retention->values();
			foreach (\HB\Modules\Settings\Models\Retention::POLICIES as $name => $allowed)
			{
				$options = ['0' => (string)$this->lang('Disabled policy')];
				foreach (array_filter($allowed) as $days) $options[(string)$days] = (string)$this->lang('%d days', $days);
				$form->rule($this->form_select($name)->title($retention_titles[$name])->size('col-6')->data($options)->value((string)$retention_values[$name])
					->check(function($post) use ($name, $allowed){
						if (!in_array((int)($post[$name] ?? -1), $allowed, TRUE)) return (string)$this->lang('Invalid duration');
					}));
			}
			$col->append($form->success(function($data){
					foreach (['privacy_controller', 'privacy_contact', 'privacy_page', 'privacy_erasure_delay', 'statistics_enabled'] as $name) $this->config($name, $data[$name], $name === 'privacy_erasure_delay' ? 'int' : NULL);
					foreach (array_keys(\HB\Modules\Settings\Models\Retention::POLICIES) as $name) $this->config($name, (int)$data[$name], 'int');
					foreach (array_keys(privacy_profile_fields()) as $field)
					{
						$key = 'privacy_profile_'.$field;
						$this->config($key, $data[$key]);
					}
					notify((string)$this->lang('Privacy settings saved'));
					refresh();
				})
				->submit((string)$this->lang('Save'))->panel()->title((string)$this->lang('Privacy'), 'fas fa-user-shield'));

			$last = $retention->last_run();
			$body = $last
				? '<div class="ui message"><div class="header">'.$this->lang('Last operation: %s', $last['mode'] === 'purge' ? $this->lang('Purge') : $this->lang('Preview')).'</div><p>'.date('Y-m-d H:i', strtotime($last['completed_at'])).' · '.($last['status'] === 'completed' ? (string)$this->lang('Completed') : (string)$this->lang('Failed')).'</p></div>'.$retention->summary($last['report'])
				: '<div class="ui message">'.$this->lang('No retention preview or purge has run yet.').'</div>';
			$body .= '<div class="retention-actions"><a href="#" class="ui button" data-modal-ajax="'.url('admin/ajax/settings/retention-preview').'">'.icon('fas fa-search').' '.$this->lang('Preview retention').'</a><a href="#" class="ui negative button" data-modal-ajax="'.url('admin/ajax/settings/retention-purge').'">'.icon('fas fa-trash-alt').' '.$this->lang('Run purge').'</a></div>';
			$col->append($this->panel()->title((string)$this->lang('Retention controls'), 'fas fa-hourglass-half')->body($body));
		});
	}

	public function copyright()
	{
		return $this->subtitle('Copyright')
					->icon('far fa-copyright')
					->_layout(function($col){
						$col->append($this	->form2()
											->info($this->html()
														->attr('class', 'alert alert-primary')
														->content('	<h5 class="alert-heading">Mots magiques</h5>
																	<dl>
																		<dt>Lien vers HiddenCMS</dt>
																			<dd>{hiddencms}</dd>
																		<dt>Nom du site</dt>
																			<dd>{name}</dd>
																		<dt>Symbole '.icon('far fa-copyright').'</dt>
																			<dd>{copyright}</dd>
																		<dt>'.$this->lang('Year').'</dt>
																			<dd>{year}</dd>
																	</dl>')
											)
											->rule('copyright', 'Copyright', $this->config->copyright)
											->success(function($data){
												$this->config('copyright', $data['copyright']);
												notify((string)$this->lang('Copyright updated'));
												refresh();
											})
											->panel()
											->title('Copyright')
						);
					});
	}

	public function _layout($callback)
	{
		$this->css('admin/settings');

		$navigation = $this->widget('navigation')->output('vertical', [
			'panel' => FALSE,
			'links' => [
				[
					'title' => (string)$this->lang('General'),
					'icon'  => 'fas fa-cog',
					'url'   => 'admin/settings'
				],
				[
					'title' => (string)$this->lang('Site identity'),
					'icon'  => 'fas fa-id-card',
					'url'   => 'admin/settings/team'
				],
				[
					'title' => (string)$this->lang('Email delivery'),
					'icon'  => 'fas fa-envelope',
					'url'   => 'admin/settings/smtp'
				],
				[
					'title' => 'Inscriptions',
					'icon'  => 'fas fa-sign-in-alt fa-rotate-90',
					'url'   => 'admin/settings/registration'
				],
				[
					'title' => (string)$this->lang('Social networks'),
					'icon'  => 'fas fa-globe',
					'url'   => 'admin/settings/socials'
				],
				[
					'title' => 'Protection anti-robots',
					'icon'  => 'fas fa-shield-alt',
					'url'   => 'admin/settings/captcha'
				],
				[
					'title' => 'Maintenance',
					'icon'  => 'fas fa-power-off',
					'url'   => 'admin/settings/maintenance'
				],
				[
					'title' => (string)$this->lang('Privacy'),
					'icon'  => 'fas fa-user-shield',
					'url'   => 'admin/settings/privacy'
				],
				[
					'title' => 'Copyright',
					'icon'  => 'far fa-copyright',
					'url'   => 'admin/settings/copyright'
				]
			]
		]);
		$menu = $this->panel()
					 ->style('settings-navigation')
					 ->heading('Sections', 'fas fa-sliders-h')
					 ->body($navigation);

		$row = $this->row(
			$left  = $this->col($menu)->size('col-3'),
			$right = $this->col()->size('col-9')
		);

		$callback($right, $left);

		return $row;
	}
}


