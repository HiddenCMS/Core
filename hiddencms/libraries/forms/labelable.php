<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries\Forms;

use HB\HiddenCMS\Library;

abstract class Labelable extends Library
{
	protected $_title;
	protected $_icon;
	protected $_placeholder;
	protected $_info;
	protected $_size;
	protected $_form;
	protected $_name;
	protected $_value;
	protected $_disabled;
	protected $_read_only;
	protected $_required;
	protected $_template = [];
	protected $_check  = [];
	protected $_filter  = [];
	protected $_errors = [];
	protected $_bind;

	public function __invoke($name)
	{
		$this->_name = $name;

		$this->_template[] = function(&$input){
			$input	->attr_if($id = $this->id(), 'id', $id)
					->attr('name', $this->_name);

			if ($this->_bind)
			{
				$this	->js('form')
						->js('form_bind');

				$input->attr('data-bind');
			}
		};

		$this->_check[] = function($post, &$data){
			if ($this->_disabled || $this->_read_only)
			{
				return FALSE;
			}
		};

		$this->_check[] = function($post, &$data){
			if ($this->_required && (!isset($post[$this->_name]) || $post[$this->_name] === ''))
			{
				$this->_errors[] = $this->lang('Veuillez remplir ce champ');
			}

			if (isset($post[$this->_name]))
			{
				$this->_value = $data[$this->_name] = $post[$this->_name];
			}
		};

		return $this;
	}

	public function __call($name, $args)
	{
		//TODO 5.6 compatibility
		if ($name == 'default')
		{
			return $this->_value;
		}

		return parent::__call($name, $args);
	}

	protected function admin_grid()
	{
		return ($theme = HB()->output->theme()) && $theme->info()->name == 'admin';
	}

	private function semantic_width($size)
	{
		$size = max(1, min(12, (int)$size));
		$width = max(1, min(16, (int)round($size * 16 / 12)));
		$words = [
			1 => 'one',
			2 => 'two',
			3 => 'three',
			4 => 'four',
			5 => 'five',
			6 => 'six',
			7 => 'seven',
			8 => 'eight',
			9 => 'nine',
			10 => 'ten',
			11 => 'eleven',
			12 => 'twelve',
			13 => 'thirteen',
			14 => 'fourteen',
			15 => 'fifteen',
			16 => 'sixteen'
		];

		return $words[$width];
	}

	private function semantic_size($size)
	{
		if (!$this->admin_grid())
		{
			return $size;
		}

		$classes = [];

		foreach (preg_split('/\s+/', trim((string)$size)) as $token)
		{
			if (preg_match('/^col-(\d+)$/', $token, $match))
			{
				$classes[] = $this->semantic_width($match[1]).' wide';
				continue;
			}

			$classes[] = $token;
		}

		$classes[] = 'field';

		return implode(' ', array_unique(array_filter($classes)));
	}
	public function __toString()
	{
		$input = NULL;

		foreach ($this->_template as $template)
		{
			if (call_user_func_array($template, [&$input]) === FALSE)
			{
				break;
			}
		}

		if (!($input = (string)$input) || !$this->_form)
		{
			return $input;
		}

		$display = $this->_form->display();

		return parent	::html()
						->attr('class', $this->admin_grid() ? 'field' : 'form-group field')
						->append_attr_if($this->_errors, 'class', $this->admin_grid() ? 'error' : 'has-danger')
						->append_attr_if($this->_size, 'class', $this->semantic_size($this->_size))
						->content($this	->array
										->append_if(($label = (string)$this->_label()) && !($display & \HB\HiddenCMS\Libraries\Form2::FORM_COMPACT), function() use ($label){
											return parent	::html(($multiple = is_a($this, 'HB\HiddenCMS\Libraries\Forms\Multiple')) ? 'legend' : 'label')
															->attr_if(!$this->admin_grid(), 'class', 'col-form-label')
															->attr_if(!$multiple, 'for', $this->_form->token().'_'.$this->_name)
															->content($label);
										})
										->append($input)
										->append_if($this->_errors && ($display & \HB\HiddenCMS\Libraries\Form2::FORM_COMPACT), function(){
											return $this->label(implode('<br />', $this->_errors), 'fas fa-exclamation-triangle')->attr('class', 'text-danger');
										})
						)
						->__toString();
	}

	public function id()
	{
		if ($this->_form)
		{
			return $this->_form->token().'_'.$this->_name;
		}
	}

	public function check($post, &$data = [])
	{
		if (is_a($post, 'closure'))
		{
			$callback = $post;

			$this->_check[] = function($post, $data) use (&$callback){
				if ($error = $callback($post, $data))
				{
					$this->_errors[] = $error;
				}
			};

			return $this;
		}
		else
		{
			foreach ($this->_check as $check)
			{
				if ($check($post, $data) === FALSE)
				{
					break;
				}
			}

			return empty($this->_errors);
		}
	}

	public function bind($callback = NULL)
	{
		if (func_num_args())
		{
			$this->_bind = $callback;
		}
		else
		{
			return $this->_bind;
		}

		return $this;
	}

	public function name()
	{
		return $this->_name;
	}

	public function title($title, $icon = NULL)
	{
		$this->_title = $this->lang($title);
		$this->_icon  = $icon;
		return $this;
	}

	public function placeholder($placeholder)
	{
		$this->_placeholder = $this->lang($placeholder);
		return $this;
	}

	public function info($info)
	{
		$this->_info = $this->lang($info);
		return $this;
	}

	public function size($size = '')
	{
		if (func_get_args())
		{
			$this->_size = $size;
			return $this;
		}
		else
		{
			return $this->_size;
		}
	}

	public function form2($form)
	{
		$this->_form = $form;
		return $this;
	}

	public function value($value, $erase = FALSE)
	{
		if ($this->_value === NULL || $erase)
		{
			$this->_value = $value;
		}

		return $this;
	}

	public function disabled()
	{
		$this->_disabled = TRUE;
		return $this;
	}

	public function read_only()
	{
		$this->_read_only = TRUE;
		return $this;
	}

	public function required()
	{
		$this->_required = TRUE;
		return $this;
	}

	public function filter()
	{
		if ($filter = func_get_args())
		{
			$this->_filter = $filter;
			return $this;
		}
		else
		{
			return $this->_filter;
		}
	}

	public function errors()
	{
		return $this->_errors;
	}

	protected function _label()
	{
		$label = $this->label($this->_title, $this->_errors ? 'fas fa-exclamation-triangle' : $this->_icon);

		if ($this->_info || $this->_errors)
		{
			$tooltip = trim(strip_tags(implode(' ', array_filter([$this->_info, implode(' ', $this->_errors)]))));

			$label	->icon_if(!$this->_errors, 'fas fa-info-circle text-info')
					->attr('data-tooltip', utf8_htmlentities($tooltip))
					->attr('data-position', 'top left');
		}

		if ($this->_required)
		{
			$label .= '<em>*</em>';
		}

		return $label;
	}

	protected function _placeholder(&$input, $placeholder = 'placeholder')
	{
		$input->attr_if($this->_placeholder, $placeholder, $this->_placeholder);

		if ($this->_form && ($this->_form->display() & \HB\HiddenCMS\Libraries\Form2::FORM_COMPACT))
		{
			$input->attr($placeholder, $this->_title ?: $this->_placeholder);
		}
	}
}


