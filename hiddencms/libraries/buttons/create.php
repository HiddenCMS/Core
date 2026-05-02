<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries\Buttons;

use HB\HiddenCMS\Library;

class Create extends Library
{
	public function __invoke($url = '', $title = '', $icon = 'fas fa-plus')
	{
		return $this->button()
					->title($title)
					->url($url)
					->icon($icon)
					->color('primary');
	}
}


