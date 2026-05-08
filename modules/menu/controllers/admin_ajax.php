<?php
/**
 * https://neofr.ag
 * @author: Michael BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Menu\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Admin_Ajax extends Controller_Module
{
	private function menu_model()
	{
		return $this->module->model2('menu');
	}

	public function _items_sort($item_id, $position)
	{
		$this->menu_model()->sort_item((int)$item_id, (int)$position);
	}
}
