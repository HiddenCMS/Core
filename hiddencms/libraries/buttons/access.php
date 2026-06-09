<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries\Buttons;

use HB\HiddenCMS\Library;

class Access extends Library
{
	public function __invoke($id, $access = '', $module = '', $title = '')
	{
		$url = 'admin/access/edit/'.($access ? ($module ?: $this->output->module()->info()->name).'/'.$id.'-'.$access : $id);

		return $this->button()
					->tooltip($title ?: $this->lang('Permissions'))
					->modal_ajax($access ? 'admin/ajax/access/edit/'.($module ?: $this->output->module()->info()->name).'/'.$id.'-'.$access : 'admin/ajax/access/edit/'.$id.'/0-default')
					->attr('data-fallback-url', url($url))
					->icon('fas fa-unlock-alt')
					->color('success')
					->compact()
					->outline();
	}
}


