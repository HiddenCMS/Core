<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries;

use HB\HiddenCMS\Library;

class Form extends Library
{
	static private $types = [
		'text',
		'password',
		'email',
		'url',
		'date',
		'datetime',
		'time',
		'number',
		'phone',
		'checkbox',
		'radio',
		'select',
		'tags',
		'file',
		'textarea',
		'editor',
		'colorpicker',
		'iconpicker',
		'legend'
	];

	static protected $_form;

	private $_buttons          = [];
	private $_confirm_deletion = [];
	private $_errors           = [];
	private $_rules            = [];
	private $_values           = [];
	private $_display_required = TRUE;
	private $_fast_mode        = FALSE;
	private $_display_captcha  = FALSE;

	static private function _token($id)
	{
		static $tokens;

		if ($tokens === NULL)
		{
			$tokens = HB()->session('form') ?: [];
		}

		if (empty($tokens[$id]))
		{
			HB()->session->set('form', $id, $tokens[$id] = unique_id(array_merge([$id], $tokens)));
		}

		return $tokens[$id];
	}

	public function __invoke()
	{
		if (!static::$_form)
		{
			static::$_form = $this;
			$this->id = $this->__id();
		}

		return static::$_form;
	}

	public function add_rules($rules, $values = [])
	{
		if (!is_array($rules))
		{
			$this->_values = $values;

			$paths = [];

			if ($path = $this->__caller->__path('forms', $rules.'.php', $paths))
			{
				include $path;
			}
			else
			{
				trigger_error('Unfound form: '.$rules.' in paths ['.implode(';', $paths).']', E_USER_WARNING);
			}
		}

		foreach ($rules as $var => $options)
		{
			if (!empty($options['rules']))
			{
				$options['rules'] = explode('|', $options['rules']);
			}

			$this->_rules[$var] = $options;
		}

		return $this;
	}

	public function add_captcha()
	{
		if (!$this->user())
		{
			$this->_display_captcha = $this->captcha->is_ok();
		}

		return $this;
	}

	public function add_back($url)
	{
		array_unshift($this->_buttons, [
			'label'  => HB()->lang('Back'),
			'action' => $this->url->back() ?: $url
		]);

		return $this;
	}

	public function add_submit($label)
	{
		$this->_buttons[] = [
			'type'  => 'submit',
			'label' => $label
		];

		return $this;
	}

	public function token($id = NULL)
	{
		if ($id === NULL)
		{
			$id = $this->id;
		}

		return self::_token($id);
	}

	public function confirm_deletion($title, $message = '')
	{
		$this->_confirm_deletion = [$title, $message];
		return $this;
	}

	public function display_required($display)
	{
		$this->_display_required = $display;
		return $this;
	}

	public function fast_mode()
	{
		$this->_fast_mode        = TRUE;
		$this->_display_required = FALSE;
		return $this;
	}

	public function is_valid(&$post = NULL)
	{
		$post = post($token = $this->token());

		if (($this->_display_captcha && !$this->captcha->is_valid()) || strtolower($_SERVER['REQUEST_METHOD']) != 'post' || (empty($post) && empty($_FILES[$token])))
		{
			return FALSE;
		}

		if ($this->_confirm_deletion)
		{
			return $post === ['delete'];
		}

		foreach ($post as $key => &$value)
		{
			if (!in_array($key, array_keys($this->_rules)))
			{
				return FALSE;
			}

			$rule_type = isset($this->_rules[$key]['type']) ? $this->_rules[$key]['type'] : NULL;

			if (is_array($value))
			{
				array_walk_recursive($value, function(&$v, $k) use ($rule_type){
					$v = $rule_type == 'editor' ? trim($v) : utf8_htmlentities(trim($v));
				});
			}
			else if ($value !== NULL)
			{
				$value = $rule_type == 'editor' ? trim($value) : utf8_htmlentities(trim($value));
			}

			unset($value);
		}

		foreach ($this->_rules as $var => $options)
		{
			if (isset($options['type']) && $options['type'] == 'legend')
			{
				continue;
			}

			if (isset($options['type']) && $options['type'] == 'iconpicker' && !empty($post[$var]) && $post[$var] == 'empty')
			{
				$post[$var] = '';
			}

			if (!is_array($options) || !isset($options['type']) || !in_array($type = $options['type'], self::$types) || !method_exists($this, '_check_'.$type))
			{
				$type = 'text';
			}

			if (($error = $this->{'_check_'.$type}($post, $var, $options)) !== TRUE)
			{
				$this->_errors[$var] = $error;
			}
		}

		if (empty($this->_errors))
		{
			if ($this->_has_upload())
			{
				$files = $_FILES[$token];

				foreach ($this->_rules as $var => $options)
				{
					if (isset($options['type']) && $options['type'] == 'file')
					{
						if (!empty($post[$var]) && $post[$var] == 'delete' && !empty($options['value']))
						{
							HB()->model2('file', $options['value'])->delete();
							$options['value'] = $post[$var] = 0;
						}

						if (!empty($files['tmp_name'][$var]))
						{
							if (!($post[$var] = HB()->model2('file')->static_uploaded_file($files, isset($options['upload']) ? $options['upload'] : NULL, isset($options['value']) ? $options['value'] : NULL, $var)->id))
							{
								$this->_errors[$var] = HB()->lang('Transfer error');
								return FALSE;
							}
							else if (isset($options['post_upload']) && is_callable($options['post_upload']))
							{
								call_user_func_array($options['post_upload'], [$post[$var]]);
							}
						}

						if (!empty($options['value']) && empty($post[$var]))
						{
							$post[$var] = $options['value'];
						}
					}
				}
			}

			return TRUE;
		}

		return FALSE;
	}

	public function get_errors()
	{
		return $this->_errors;
	}

	public function value($var)
	{
		return isset($this->_values[$var]) ? $this->_values[$var] : NULL;
	}

	private function _check_text($post, $var, $options)
	{
		if (!empty($options['rules']) && in_array('disabled', $options['rules']))
		{
			return TRUE;
		}

		if (!in_array($post[$var], ['', NULL]) &&
			!empty($options['values']) &&
			is_array($options['values']) &&
			is_array($post[$var]) &&
			array_diff(array_filter($post[$var]), array_map('utf8_htmlentities', array_keys($options['values'])))
		)
		{
			return HB()->lang('The selected value is not valid| The selected values are not valid', count($post[$var]));
		}

		$is_file = !empty($options['type']) && $options['type'] == 'file';

		if (	!empty($options['rules']) &&
				in_array('required', $options['rules']) &&
				(
					($is_file && empty($_FILES[$this->token()]['tmp_name'][$var])) ||
					(!$is_file && in_array($post[$var], ['', NULL]))
				)
			)
		{
			return HB()->lang('Please fill this field');
		}

		if ($is_file && !empty($_FILES[$this->token()]['error'][$var]) && $_FILES[$this->token()]['error'][$var] != 4)
		{
			$errors = [
				1 => 'The uploaded file exceeds upload_max_filesize in php.ini',
				2 => 'The uploaded file exceeds MAX_FILE_SIZE in the HTML form',
				3 => 'The file was only partially uploaded',
				4 => 'No file was uploaded',
				6 => 'A temporary folder is missing',
				7 => 'Failed to write the file to disk',
				8 => 'A PHP extension stopped the file upload'
			];

			return HB()->lang($errors[$_FILES[$this->token()]['error'][$var]] ?? 'Unknown upload error');
		}

		if (isset($options['check']) && is_callable($options['check']))
		{
			if (!empty($options['type']) && $options['type'] == 'file')
			{
				$error = !empty($_FILES[$this->token()]['tmp_name'][$var]) ? call_user_func_array($options['check'], [$_FILES[$this->token()]['tmp_name'][$var], extension($_FILES[$this->token()]['name'][$var])]) : TRUE;
			}
			else
			{
				$error = call_user_func_array($options['check'], [$post[$var], $post]);
			}

			if (!in_array($error, [TRUE, NULL], TRUE))
			{
				return $error;
			}
		}

		return TRUE;
	}

	private function _check_file(&$post, $var, $options)
	{
		if (empty($post[$var]))
		{
			$post[$var] = NULL;
		}

		return $this->_check_text($post, $var, $options);
	}

	private function _check_checkbox(&$post, $var, $options)
	{
		$post[$var] = array_filter(isset($post[$var]) ? $post[$var] : [], function($a){
			return strlen($a);
		});
		return $this->_check_text($post, $var, $options);
	}

	private function _check_email($post, $var, $options)
	{
		if ($post[$var] !== '' && !is_valid_email($post[$var]))
		{
			return HB()->lang('Please enter a valid email address');
		}

		return $this->_check_text($post, $var, $options);
	}

	private function _check_url($post, $var, $options)
	{
		if ($post[$var] !== '' && !is_valid_url($post[$var]))
		{
			return HB()->lang('Please enter a valid URL');
		}

		return $this->_check_text($post, $var, $options);
	}

	private function _check_number(&$post, $var, $options)
	{
		if ($post[$var] !== '')
		{
			$post[$var] = str_replace(',', '.', trim((string)$post[$var]));

			if (!is_numeric($post[$var]))
			{
				return (string)$this->lang('Invalid number');
			}
		}

		return $this->_check_text($post, $var, $options);
	}

	private function _check_phone(&$post, $var, $options)
	{
		if ($post[$var] !== '' && !preg_match('/^0[1-9]([. ]?)\d{2}(?:\1\d{2}){3}$/', $post[$var], $match))
		{
			return (string)$this->lang('Invalid phone number');
		}

		return $this->_check_text($post, $var, $options);
	}

	private function _check_datetime(&$post, $var, $options)
	{
		$this->config->lang->datetime2sql($post[$var]);
		return $this->_check_text($post, $var, $options);
	}

	private function _check_time(&$post, $var, $options)
	{
		$this->config->lang->time2sql($post[$var]);
		return $this->_check_text($post, $var, $options);
	}

	private function _check_date(&$post, $var, $options)
	{
		$this->config->lang->date2sql($post[$var]);
		return $this->_check_text($post, $var, $options);
	}

	private function _check_editor(&$post, $var, $options)
	{
		$post[$var] = trim($post[$var]);
		$post[$var] = preg_replace('~^(?:<(?:p|div)>(?:&nbsp;|\s|<br\s*/?>)*</(?:p|div)>\s*)+$~i', '', $post[$var]);
		$post[$var] = trim($post[$var]);

		return $this->_check_text($post, $var, $options);
	}

	public function display()
	{
		if ($this->_confirm_deletion)
		{
			list($title, $message) = $this->_confirm_deletion;

			if ($this->url->ajax())
			{
				return $this->_render('confirm_delete', [
					'title'        => $title,
					'message'      => $message,
					'close_label'  => HB()->lang('Close'),
					'cancel_label' => HB()->lang('Cancel'),
					'delete_label' => HB()->lang('Delete'),
					'request_url'  => url($this->url->request),
					'token'        => $this->token()
				]);
			}

			return;
		}

		$fields = [];

		if ($has_upload = $this->_has_upload())
		{
			$this->js('file');
		}

		$post = post($this->token());

		foreach ($this->_rules as $var => $options)
		{
			if (!is_array($options) || !isset($options['type']) || !in_array($type = $options['type'], self::$types))
			{
				$type = 'text';
			}

			if ($display = $this->{'_display_'.$type}($var, $options, isset($post[$var]) ? $post[$var] : NULL))
			{
				if ($type == 'legend')
				{
					$fields[] = $display;
				}
				else
				{
					$is_required = isset($options['rules']) && in_array('required', $options['rules']);

					$fields[] = $this->_render('field', [
						'content'          => $display,
						'fast_mode'        => $this->_fast_mode,
						'label'            => !empty($options['label']) ? $options['label'] : '',
						'label_tag'        => in_array($type, ['radio', 'checkbox']) ? 'legend' : 'label',
						'label_for'        => !in_array($type, ['radio', 'checkbox']) ? 'form_'.$this->token().'_'.$var : '',
						'description'      => !empty($options['description']) ? $options['description'] : '',
						'error'            => !empty($this->_errors[$var]) ? $this->_errors[$var] : '',
						'has_error'        => isset($this->_errors[$var]),
						'is_required'      => $is_required,
						'display_required' => $this->_display_required,
						'size'             => !empty($options['size']) ? $options['size'] : ''
					]);
				}
			}
		}

		if ($this->_display_captcha)
		{
			HB()->js('captcha');
			$fields[] = $this->_render('captcha', [
				'content'   => $this->captcha->display(),
				'fast_mode' => $this->_fast_mode
			]);
		}

		if ($this->_display_required)
		{
			$fields[] = $this->_render('required_note', [
				'message' => HB()->lang('* All fields marked with a star are required')
			]);
		}

		if (!empty($this->_buttons))
		{
			$buttons = [];

			foreach ($this->_buttons as $button)
			{
				$buttons[] = $this->_display_button($button);
			}

			$fields[] = $this->_render('actions', [
				'buttons'   => array_filter($buttons),
				'fast_mode' => $this->_fast_mode
			]);
		}

		$output = $this->_render('form', [
			'action'     => url($this->url->request),
			'has_upload' => $has_upload,
			'content'    => implode('', $fields)
		]);

		$this->save();

		return $output;
	}

	public function save()
	{
		static::$_form = NULL;
		return $this;
	}

	public function set_id($id)
	{
		$this->id = $id;
		return $this;
	}

	private function _display_button($button)
	{
		if (isset($button['type']) && $button['type'] == 'submit')
		{
			return $this->_render('button', [
				'tag'     => 'button',
				'type'    => 'submit',
				'variant' => 'primary',
				'label'   => $button['label'],
				'url'     => ''
			]);
		}
		else if (!empty($button['label']) && !empty($button['action']))
		{
			return $this->_render('button', [
				'tag'     => 'a',
				'type'    => '',
				'variant' => 'secondary',
				'label'   => $button['label'],
				'url'     => url($button['action'])
			]);
		}

		return '';
	}

	private function _display_value($var, $options)
	{
		$post = post();
		$type = isset($options['type']) ? $options['type'] : NULL;

		if (isset($post[$this->token()][$var]))
		{
			if (is_array($post[$this->token()][$var]))
			{
				return array_values(array_filter($post[$this->token()][$var]));
			}
			else
			{
				$value = trim($post[$this->token()][$var]);

				if ($type == 'editor')
				{
					return $value;
				}

				return utf8_htmlentities($value);
			}
		}
		else if (isset($options['checked']))
		{
			return array_keys(array_filter($options['checked']));
		}
		else if (isset($options['value']))
		{
			$value = (string)$options['value'];

			if ($type == 'editor' && !preg_match('/<\s*[a-z][^>]*>/i', $value))
			{
				return bbcode($value);
			}

			return $value;
		}

		if (isset($options['default']))
		{
			$value = (string)$options['default'];

			if ($type == 'editor' && !preg_match('/<\s*[a-z][^>]*>/i', $value))
			{
				return bbcode($value);
			}

			return $value;
		}

		return '';
	}

	private function _render($component, array $data = [])
	{
		return $this->template->render('legacy_form/'.$component, $data);
	}

	private function admin_grid()
	{
		return $this->url->admin || (($theme = HB()->output->theme()) && $theme->info()->name == 'admin');
	}

	private function _display_text($var, $options, $post, $type = 'text')
	{
		$classes = [];
		$calendar = NULL;

		if (in_array($type, ['date', 'datetime', 'time']))
		{
			$types = ['date' => 'L', 'datetime' => 'L LT', 'time' => 'LT'];

			if ($this->admin_grid())
			{
				$calendar = [
					'type'   => $type,
					'format' => $types[$type],
					'locale' => $this->config->lang->info()->name
				];

				HB()	->js('bootstrap-datetimepicker/moment.min')
						->js('bootstrap-datetimepicker/locales/'.$calendar['locale'])
						->js('form')
						->js('form_calendar');
			}
			else
			{
				HB()	->css('bootstrap-datetimepicker.min')
						->js('bootstrap-datetimepicker/moment.min')
						->js('bootstrap-datetimepicker/bootstrap-datetimepicker.min')
						->js('bootstrap-datetimepicker/locales/'.$this->config->lang->info()->name)
						->js_load('$(".'.$type.'").datetimepicker({allowInputToggle: true, locale: "'.$this->config->lang->info()->name.'", format: "'.$types[$type].'"});');
			}

			$classes[] = $type;

			if (empty($options['icon']))
			{
				$options['icon'] = $type == 'time' ? 'far fa-clock' : 'fas fa-calendar-alt';
			}

			$type = 'text';
		}
		else if ($type == 'email')
		{
			$type = 'text';

			if (empty($options['icon']))
			{
				$options['icon'] = 'far fa-envelope';
			}
		}
		else if ($type == 'url')
		{
			$type = 'text';

			if (empty($options['icon']))
			{
				$options['icon'] = 'fas fa-globe';
			}
		}
		else if ($type == 'phone')
		{
			$type = 'text';

			if (empty($options['icon']))
			{
				$options['icon'] = 'fas fa-phone';
			}
		}
		else if ($type == 'colorpicker')
		{
			$type = 'text';

			$classes[] = 'color';

			$options['icon'] = FALSE;

			if ($this->admin_grid())
			{
				HB()->js('colorpicker');
			}
			else
			{
				HB()	->css('bootstrap-colorpicker.min')
						->js('bootstrap-colorpicker.min')
						->js('colorpicker');
			}
		}

		$attrs = [
			'id'   => 'form_'.$this->token().'_'.$var,
			'name' => $this->token().'['.$var.']',
			'type' => $type
		];

		foreach (['min', 'max', 'step'] as $attribute)
		{
			if (isset($options[$attribute]))
			{
				$attrs[$attribute] = $options[$attribute];
			}
		}

		if ($calendar)
		{
			$attrs['data-calendar-type']   = $calendar['type'];
			$attrs['data-calendar-format'] = $calendar['format'];
			$attrs['data-calendar-locale'] = $calendar['locale'];
			$attrs['autocomplete']         = 'off';
		}

		if ($type != 'file')
		{
			$attrs['value'] = $this->_display_value($var, $options);

			if (!empty($options['placeholder']))
			{
				$attrs['placeholder'] = $options['placeholder'];
			}
			else if ($this->_fast_mode && !empty($options['label']))
			{
				$attrs['placeholder'] = $options['label'];
			}
		}

		if ($type == 'password' && isset($options['autocomplete']) && $options['autocomplete'] === FALSE)
		{
			$attrs['autocomplete'] = 'off';
		}

		if (!empty($options['rules']) && in_array('disabled', $options['rules']))
		{
			$attrs['disabled'] = NULL;
		}

		$input = $this->_render('input', [
			'attrs'  => $attrs,
			'is_file' => $type == 'file'
		]);

		if ($type == 'file')
		{
			$post = post();
			$deleted = isset($post[$this->token()][$var]) && $post[$this->token()][$var] == 'delete';
			$thumbnail = '';

			if (!empty($options['value']))
			{
				if (!$deleted)
				{
					$thumbnail = url($this->db->select('path')->from('file')->where('id', $options['value'])->row());
				}
			}

			$input = $this->_render('file', [
				'input'        => $input,
				'info'         => !empty($options['info']) ? $options['info'] : '',
				'upload_label' => HB()->lang('Upload file'),
				'delete_label' => HB()->lang('Delete'),
				'confirm_label' => HB()->lang('Delete the file?'),
				'delete_name'  => $this->token().'['.$var.']',
				'deleted'      => $deleted,
				'thumbnail'    => $thumbnail
			]);
		}

		if (isset($options['icon']))
		{
			$input = $this->_render('input_wrapper', [
				'input'   => $input,
				'classes' => implode(' ', $classes),
				'icon'    => $options['icon'] ? icon($options['icon']) : '',
				'color'   => in_array('color', $classes)
			]);
		}

		return $input;
	}

	private function _display_iconpicker($var, $options, $post)
	{
		HB()	->css('bootstrap-iconpicker.min')
					->js('bootstrap-iconpicker.bundle.min')
					->js_load('	$(".iconpicker").iconpicker({
									arrowPrevIconClass: "fas fa-caret-left",
									arrowNextIconClass: "fas fa-caret-right",
									cols: 10,
									rows: 5,
									iconset: "fontawesome",
									labelHeader: "'.HB()->lang('{0} on {1} pages').'",
									labelFooter: "'.HB()->lang('{2} icons').'",
									searchText: "'.HB()->lang('Search...').'",
									selectedClass: "active",
									unselectedClass: ""
								});');

		return $this->_render('iconpicker', [
			'id'        => 'form_'.$this->token().'_'.$var,
			'name'      => $this->token().'['.$var.']',
			'data_icon' => $this->_display_value($var, $options),
			'has_error' => isset($this->_errors[$var])
		]);
	}

	private function _display_colorpicker($var, $options, $post)
	{
		return $this->_display_text($var, $options, $post, 'colorpicker');
	}

	private function _display_password($var, $options, $post)
	{
		return $this->_display_text($var, $options, $post, 'password');
	}

	private function _display_date($var, $options, $post)
	{
		if (isset($options['value']) && $options['value'] !== '')
		{
			$options['value'] = timetostr(HB()->lang('d/m/Y'), $options['value']);
		}
		else
		{
			$options['value'] = '';
		}

		return $this->_display_text($var, $options, $post, 'date');
	}

	private function _display_datetime($var, $options, $post)
	{
		if (isset($options['value']) && $options['value'] !== '')
		{
			$options['value'] = timetostr(HB()->lang('d/m/Y H:i'), $options['value']);
		}
		else
		{
			$options['value'] = '';
		}

		return $this->_display_text($var, $options, $post, 'datetime');
	}

	private function _display_time($var, $options, $post)
	{
		if (isset($options['value']) && $options['value'] !== '' && $options['value'] !== '00:00:00')
		{
			$options['value'] = timetostr(HB()->lang('H:i'), $options['value']);
		}
		else
		{
			$options['value'] = '';
		}

		return $this->_display_text($var, $options, $post, 'time');
	}

	private function _display_number($var, $options, $post)
	{
		return $this->_display_text($var, $options, $post, 'number');
	}

	private function _display_phone($var, $options, $post)
	{
		return $this->_display_text($var, $options, $post, 'phone');
	}

	private function _display_email($var, $options, $post)
	{
		return $this->_display_text($var, $options, $post, 'email');
	}

	private function _display_url($var, $options, $post)
	{
		return $this->_display_text($var, $options, $post, 'url');
	}

	private function _display_file($var, $options, $post)
	{
		return $this->_display_text($var, $options, $post, 'file');
	}

	private function _display_tags($var, $options, $post)
	{
		return $this->_display_text($var, $options, $post, 'tags');
	}

	private function _display_checkbox($var, $options, $post)
	{
		$items = [];

		if (!empty($options['values']))
		{
			$user_value = (array)$this->_display_value($var, $options);

			foreach ($options['values'] as $value => $label)
			{
				$items[] = [
					'type'    => 'checkbox',
					'name'    => $this->token().'['.$var.'][]',
					'value'   => $value,
					'label'   => $label,
					'checked' => in_array((string)$value, $user_value)
				];
			}
		}

		return $this->_render('choices', [
			'hidden_name' => $this->token().'['.$var.'][]',
			'items'       => $items,
			'inline'      => FALSE
		]);
	}

	private function _display_radio($var, $options, $post)
	{
		$items = [];

		if (!empty($options['values']))
		{
			$user_value = $this->_display_value($var, $options);

			foreach ($options['values'] as $value => $label)
			{
				$items[] = [
					'type'    => 'radio',
					'name'    => $this->token().'['.$var.']',
					'value'   => $value,
					'label'   => $label,
					'checked' => $user_value == (string)$value
				];
			}
		}

		return $this->_render('choices', [
			'hidden_name' => $this->token().'['.$var.']',
			'items'       => $items,
			'inline'      => TRUE
		]);
	}

	private function _display_select($var, $options, $post)
	{
		if (empty($options['values']) && (!isset($options['rules']) || !in_array('required', $options['rules'])))
		{
			return;
		}

		$choices = [];
		$user_value = $this->_display_value($var, $options);

		if (!empty($options['values']))
		{
			foreach ($options['values'] as $value => $label)
			{
				$choices[] = [
					'value'    => $value,
					'label'    => $label,
					'selected' => $user_value == (string)$value
				];
			}
		}

		return $this->_render('select', [
			'id'          => 'form_'.$this->token().'_'.$var,
			'name'        => $this->token().'['.$var.']',
			'placeholder' => !isset($options['rules']) || !in_array('required', $options['rules']),
			'choices'     => $choices
		]);
	}

	private function _display_textarea($var, $options, $post, $editor = FALSE)
	{
		return $this->_render('textarea', [
			'id'     => 'form_'.$this->token().'_'.$var,
			'name'   => $this->token().'['.$var.']',
			'rows'   => !empty($options['rows']) ? (int)$options['rows'] : 10,
			'value'  => $this->_display_value($var, $options),
			'editor' => $editor
		]);
	}

	private function _display_editor($var, $options, $post)
	{
		$this	->css('file_picker')
				->js('file_picker')
				->js('tinymce/tinymce.min')
				->js('form_tinymce');

		return $this->_display_textarea($var, $options, $post, TRUE);
	}

	private function _display_legend($var, $options, $post)
	{
		return $this->_render('legend', [
			'label' => !empty($options['label']) ? $options['label'] : ''
		]);
	}

	private function _has_upload()
	{
		foreach ($this->_rules as $var => $options)
		{
			if (isset($options['type']) && $options['type'] == 'file')
			{
				return TRUE;
			}
		}

		return FALSE;
	}
}
