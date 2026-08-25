<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries\Forms;

class Radio extends Multiple
{
	protected $_type   = 'radio';
	protected $_inline = TRUE;

	public function __invoke($name)
	{
		parent::__invoke($name);

		$this->_template[0] = function(&$input){
			$items = [];
			$i = 0;

			foreach ($this->_data as $value => $label)
			{
				$control = $this	->html('input', TRUE)
								->attr('type',  $this->_type)
								->attr('id',    $id = implode('_', [$this->_form->token(), $this->_name, ++$i]))
								->attr('name',  $this->_name)
								->attr('value', $value)
								->attr_if($this->_disabled, 'disabled')
								->attr_if($this->_read_only, 'readonly');

				$this->_value($control, $value);

				if ($this->_bind)
				{
					$this	->js('form')
							->js('form_bind');

					$control->attr('data-bind');
				}

				$items[] = [
					'input' => (string)$control,
					'label' => $label,
					'id'    => $id
				];
			}

			$data = [
				'items'  => $items,
				'type'   => $this->_type,
				'inline' => $this->_inline || ($this->_form->display() & \HB\HiddenCMS\Libraries\Form2::FORM_INLINE)
			];

			$input = $this->template->render('forms/choices', $data, $this->legacy_choices($data));
		};

		return $this;
	}

	private function legacy_choices(array $data)
	{
		$output = [];

		foreach ($data['items'] as $item)
		{
			$output[] = '<div class="form-check'.($data['inline'] ? ' form-check-inline' : '').'">'
						.$item['input']
						.'<label class="form-check-label" for="'.$item['id'].'">'.$item['label'].'</label>'
						.'</div>';
		}

		return implode('', $output);
	}

	public function inline($inline)
	{
		$this->_inline = $inline;
		return $this;
	}

	protected function _value(&$input, $value)
	{
		$input->attr_if((string)$this->_value === (string)$value, 'checked');
	}
}
