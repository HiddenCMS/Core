<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries\Buttons;

use HB\HiddenCMS\Library;

class Delete extends Library
{
	public function __invoke($url = '', $title = NULL)
	{
		return $this->js('delete')
					->button()
					->tooltip($title ?: $this->lang('Supprimer'))
					->url($url)
					->icon('fas fa-times')
					->color('danger')
					->style_if($url, 'delete')//TODO
					->compact()
					->outline();
	}
}


