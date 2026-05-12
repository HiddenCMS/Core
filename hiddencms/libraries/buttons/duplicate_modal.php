<?php
/**
 * https://neofr.ag
 * @author: Michael BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries\Buttons;

use HB\HiddenCMS\Library;

class Duplicate_Modal extends Library
{
	public function __invoke($modal, $title = '')
	{
		return $this->button_duplicate('', $title)
					->modal($modal);
	}
}

