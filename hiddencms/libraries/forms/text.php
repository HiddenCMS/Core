<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries\Forms;

class Text extends Labelable
{
	protected $_type   = 'text';
	protected $_data   = [];
	protected $_addons = [];
	protected $_iconpicker;

	public function __invoke($name)
	{
		$this->_template[] = function(&$input){
			$input = $this	->html('input', TRUE)
							->attr('type', $this->_type);

			if ($this->_value !== '')
			{
				$input->attr('value', $this->_value);
			}

			if ($this->_disabled)
			{
				$input->attr('disabled');
			}

			if ($this->_read_only)
			{
				$input->attr('readonly');
			}

			if (is_a($this, 'HB\HiddenCMS\Libraries\Forms\Password'))
			{
				$input->attr('autocomplete');
			}

			if ($this->_placeholder)
			{
				$input->attr('placeholder', $this->_placeholder);
			}

			if ($this->_form && ($this->_form->display() & \HB\HiddenCMS\Libraries\Form2::FORM_COMPACT))
			{
				$input->attr('placeholder', $this->_title ?: $this->_placeholder);
			}

			if ($this->_data)
			{
				$this	->css('jquery-ui.min')
						->css('form_text')
						->js('jquery-ui.min')
						->js('form')
						->js('form_text');

				$encode = function($data){
					if (method_exists($data, '__toArray'))
					{
						$data = $data->__toArray();
					}

					array_walk($data, function(&$value){
						$value = utf8_html_entity_decode($value);
					});

					natsort($data);

					return utf8_htmlentities(json_encode(array_values($data)));
				};

				$input	->class('autocomplete')
						->attr('data-source', $encode($this->_data));
			}
		};

		parent::__invoke($name);

		$this->_check[] = function($post, &$data){
			if ($this->_iconpicker)
			{
				if (!$this->_iconpicker[0]->check($post, $data))
				{
					$this->_errors = array_merge($this->_errors, $this->_iconpicker[0]->errors());
				}
			}
		};

		$this->_template[] = function(&$input){
			$addons = [
				'prepend' => [],
				'append'  => []
			];

			$add_group = function($addon, $align) use (&$addons){
				if (!in_array($align, ['prepend', 'append']))
				{
					$align = 'prepend';
				}

				$addons[$align][] = (string)$addon;
			};

			if ($this->_iconpicker)
			{
				list($iconpicker, $align) = $this->_iconpicker;

				$iconpicker->disabled_if($this->_disabled || $this->_read_only);

				$add_group($iconpicker, $align);
			}

			foreach ($this->_addons as $addon)
			{
				$add_group($addon, $addon->align());
			}

			if ($addons['prepend'] || $addons['append'])
			{
				$input = $this->template->render('forms/input_group', [
					'input'   => $input,
					'prepend' => $addons['prepend'],
					'append'  => $addons['append']
				], $this->legacy_input_group($input, $addons));
			}
		};

		return $this;
	}

	private function legacy_input_group($input, array $addons)
	{
		if ($this->admin_grid())
		{
			return $this	->html()
						->attr('class', 'ui labeled input')
						->append_attr_if($addons['append'] && !$addons['prepend'], 'class', 'right')
						->content(implode('', $addons['prepend']).$input.implode('', $addons['append']))
						->__toString();
		}

		$prepend = '';
		foreach ($addons['prepend'] as $addon)
		{
			$prepend .= '<div class="input-group-prepend"><div class="input-group-text">'.$addon.'</div></div>';
		}

		$append = '';
		foreach ($addons['append'] as $addon)
		{
			$append .= '<div class="input-group-append"><div class="input-group-text">'.$addon.'</div></div>';
		}

		return '<div class="input-group">'.$prepend.$input.$append.'</div>';
	}

	public function data($data)
	{
		$this->_data = $data;
		return $this;
	}

	public function addon($label, $align = 'prepend')
	{
		if (!is_a($label, 'HB\HiddenCMS\Libraries\Label'))
		{
			$label = $this	->label()
							->icon($label)
							->align($align);
		}

		$this->_addons[] = $label;
		return $this;
	}

	public function iconpicker($name, $value = '', $required = FALSE, $align = 'left')
	{
		$this->_iconpicker = [parent::form_iconpicker($name)->value($value)->required_if($required), $align];
		return $this;
	}
}
