<?php
/**
 * https://neofr.ag
 * @author: Michael BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries\Buttons;

use HB\HiddenCMS\Library;

class Delete extends Library
{
	public function __invoke($url = '', $title = NULL)
	{
		return $this->js('delete')
					->button()
					->component('buttons/delete')
					->tooltip($title ?: $this->lang('Supprimer'))
					->url($url)
					->icon('fas fa-times')
					->style_if($url, 'delete')
					->style('hb-btn hb-btn-danger hb-btn-sm hb-btn-icon');
	}
}
