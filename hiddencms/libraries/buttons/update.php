<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries\Buttons;

use HB\HiddenCMS\Library;

class Update extends Library
{
	public function __invoke($url = '', $title = '')
	{
		return $this->button()
					->tooltip($title ?: $this->lang('Éditer'))
					->url($url)
					->icon('fas fa-pencil-alt')
					->color('info')
					->compact()
					->outline();
	}
}
