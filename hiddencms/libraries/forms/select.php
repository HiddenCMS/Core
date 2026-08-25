<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries\Forms;

class Select extends Multiple
{
	const SELECT_MULTIPLE = 1;
	const SELECT_CREATE   = 2;

	protected $_optgroup = [];
	protected $_render;
	protected $_search;
	protected $_create;

	public function __invoke($name)
	{
		$this->_template[] = function(&$input){
			$encode = function($data){
				if ((is_string($data) || is_object($data)) && method_exists($data, '__toArray'))
				{
					$data = $data->__toArray();
				}

				array_walk($data, function(&$value, $key){
					$value = array_merge([$key], array_map('utf8_html_entity_decode', (array)$value));
				});

				return utf8_htmlentities(json_encode(array_values($data)));
			};

			$input = $this->html('select');

			$input->attr('data-options', $encode($this->_data));

			$classes = ['selectize'];

			if ($this->_multiple)
			{
				$input->attr('multiple');
			}

			if ($this->_disabled || $this->_read_only)
			{
				$input->attr('disabled');
			}

			if (isset($this->_render[0]) && $this->_render[0] !== '')
			{
				$input->attr('data-render-option', utf8_htmlentities($this->_render[0]));
			}

			if ($this->_search)
			{
				$input->attr('data-search-field', $this->_search + 1);
			}

			if (!is_empty($this->_value))
			{
				$input->attr('data-value', implode(',', (array)$this->_value));
			}

			if (!empty($this->_optgroup) && isset($this->_optgroup[0], $this->_optgroup[1]))
			{
				$input->attr('data-optgroups', $encode($this->_optgroup[1]));
				$input->attr('data-optgroup-field', $this->_optgroup[0] + 1);

				if (isset($this->_render[1]) && $this->_render[1] !== '')
				{
					$input->attr('data-render-optgroup', $this->_render[1]);
				}
			}

			$this	->css('selectize')
					->js('selectize.min')
					->js('form')
					->js('form_select');

			if ($this->_placeholder)
			{
				$input->attr('data-placeholder', $this->_placeholder);
			}

			if ($this->_form && ($this->_form->display() & \HB\HiddenCMS\Libraries\Form2::FORM_COMPACT))
			{
				$input->attr('data-placeholder', $this->_title ?: $this->_placeholder);
			}

			$input->class($classes);
		};

		parent::__invoke($name);

		$this->_template[] = function(&$input){
			if ($this->_multiple)
			{
				$input->append_attr('name', '[]', '');
			}
		};

		return $this;
	}

	protected function _label()
	{
		$label = parent::_label();

		if (!$this->_disabled && !$this->_read_only && $this->_create && ($model = $this->_form->model($this)) && ($action = $model->action('create')) && ($button = $action->__button()))
		{
			$label .= $button;
		}

		return $label;
	}

	public function create()
	{
		$this->_create = TRUE;
		return $this;
	}

	public function optgroup($field, $optgroup)
	{
		$this->_optgroup = [$field, $optgroup];
		return $this;
	}

	public function render($render, $optgroup = '')
	{
		$this->_render = [$render, $optgroup];
		return $this;
	}

	public function search($search)
	{
		$this->_search = $search;
		return $this;
	}

	public function multiple($allow_create = FALSE)
	{
		$this->_multiple = self::SELECT_MULTIPLE;

		if ($allow_create)
		{
			$this->_multiple |= self::SELECT_CREATE;
		}

		return $this;
	}
}
