<?php
/**
 * https://neofr.ag
 * @author: MichaÃƒÂ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries\Forms;

class Iconpicker extends Labelable
{
	public function __invoke($name)
	{
		$this->_template[] = function(&$input){
			$this	->css('bootstrap-iconpicker.min')
					->js('bootstrap-iconpicker.bundle.min')
					->js('iconpicker');

			$input = $this	->html('button')
							->attr('type', 'button')
							->attr('class', 'btn btn-'.($this->_errors ? 'danger' : 'light').' iconpicker')
							->attr('data-icon', $this->_value)
							->attr_if($this->_disabled || $this->_read_only, 'disabled')
							->content('<i class="fa"></i>');
		};

		parent::__invoke($name);

		$this->_check[] = function($post, &$data){
			if ($this->_required && (!isset($post[$this->_name]) || is_empty($post[$this->_name]) || $post[$this->_name] == 'empty'))
			{
				$this->_errors[] = 'Veuillez sÃƒÂ©lectionner une icÃƒÂ´ne';
			}
		};

		return $this;
	}
}
