<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries\Buttons;

use HB\HiddenCMS\Library;

class Back extends Library
{
	public function __invoke($url = '', $title = '')
	{
		return $this->button()
					->component('buttons/back')
					->title($title ?: $this->lang('Retour'))
					->url($this->url->back() ?: $url);
	}
}


