<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries\Forms;

class Colorpicker extends Text
{
	public function __invoke($name)
	{
		parent::__invoke($name);

		$this->_check[] = function($post, &$data){
			if (isset($post[$this->_name]) && $post[$this->_name] !== '' && !get_colors($post[$this->_name]))
			{
				$this->_errors[] = (string)$this->lang('Invalid color');
			}
		};

		array_splice($this->_template, 1, 0, function(&$input){
			if ($this->admin_grid())
			{
				$this->js('colorpicker');
			}
			else
			{
				$this	->css('bootstrap-colorpicker.min')
						->js('bootstrap-colorpicker.min')
						->js('colorpicker');
			}

			$input->append_attr('class', 'color');
		});

		return $this->addon('fas fa-eye-dropper', 'right')
					->addon($this->label()->title('<i></i>'))
					->size('col-3');
	}
}
