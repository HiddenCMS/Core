<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries\Buttons;

use HB\HiddenCMS\Libraries\Button;

class Dropdown extends Button
{
	protected $_dropdown = [];

	public function __invoke()
	{
		parent::__invoke();

		$this->_container = function($content){
			$dropdown = '';

			if ($this->_dropdown)
			{
				$dropdown = $this	->html()
									->attr('class', 'dropdown-menu')
									->content(array_map(function($a){
										return $this->html()
													->attr('class', 'dropdown-item')
													->content($a);
									}, $this->_dropdown));
			}

			return $this->html()
						->attr('class', 'btn-group')
						->content($content.$dropdown);
		};

		return $this->data('toggle', 'dropdown')
					->tag('button')
					->attr('type', 'button')
					->append_attr('class', 'dropdown-toggle');
	}

	public function dropdown($dropdown)
	{
		$this->_dropdown = $dropdown;
		return $this;
	}
}


