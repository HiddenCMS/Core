<?php
/**
 * https://neofr.ag
 * @author: Michael BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries\Buttons;

use HB\HiddenCMS\Library;

class Update extends Library
{
	public function __invoke($url = '', $title = '')
	{
		return $this->button()
					->component('buttons/update')
					->tooltip($title ?: $this->lang('Editer'))
					->url($url)
					->icon('fas fa-pencil-alt')
					->style('hb-btn hb-btn-info hb-btn-sm hb-btn-icon');
	}
}
