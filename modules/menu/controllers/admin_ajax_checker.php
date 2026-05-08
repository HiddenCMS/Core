<?php
/**
 * https://neofr.ag
 * @author: Michael BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Menu\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module_Checker;

class Admin_Ajax_Checker extends Module_Checker
{
	private function menu_model()
	{
		return $this->module->model2('menu');
	}

	public function _items_sort()
	{
		if (!$this->is_authorized('modify_menus'))
		{
			$this->error->unauthorized();
		}

		if (($check = post_check('id', 'position')) && $this->menu_model()->check_item((int)$check['id']))
		{
			return [(int)$check['id'], (int)$check['position']];
		}
	}
}
