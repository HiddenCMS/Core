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

	public function _items_sort($item_id_or_order, $position = NULL)
	{
		if (is_array($item_id_or_order))
		{
			$this->menu_model()->sort_items($item_id_or_order);
			return;
		}

		$this->menu_model()->sort_item((int)$item_id_or_order, (int)$position);
	}
}
