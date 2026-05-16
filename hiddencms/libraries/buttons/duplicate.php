<?php
/**
 * https://neofr.ag
 * @author: Michael BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries\Buttons;

use HB\HiddenCMS\Library;

class Duplicate extends Library
{
	public function __invoke($url = '', $title = '')
	{
		return $this->button()
					->component('buttons/duplicate')
					->tooltip($title ?: $this->lang('Dupliquer'))
					->url($url)
					->icon('far fa-copy');
	}
}
