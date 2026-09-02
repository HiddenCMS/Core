<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries\Forms;

class Textarea extends Labelable
{
	protected $_rows = 15;

	public function __invoke($name)
	{
		$this->_template[] = function(&$input){
			$input = parent	::html('textarea')
							->attr('rows', $this->_rows);

			if ($this->_disabled)
			{
				$input->attr('disabled');
			}

			if ($this->_read_only)
			{
				$input->attr('readonly');
			}

			if ($this->_placeholder)
			{
				$input->attr('placeholder', $this->_placeholder);
			}

			if ($this->_form && ($this->_form->display() & \HB\HiddenCMS\Libraries\Form2::FORM_COMPACT))
			{
				$input->attr('placeholder', $this->_title ?: $this->_placeholder);
			}

			$input->content(utf8_htmlentities($this->_value));
		};

		return parent::__invoke($name);
	}

	public function rows($rows)
	{
		$this->_rows = $rows;
		return $this;
	}
}
