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

		$order = post('order');
		if (is_array($order) && !empty($order))
		{
			$order = array_values(array_filter(array_map('intval', $order), function($id){
				return $id > 0;
			}));

			if (!empty($order))
			{
				return [$order];
			}
		}

		$id = post('id');
		$position = post('position');

		if ($id !== NULL && $position !== NULL && is_numeric($id) && is_numeric($position))
		{
			return [(int)$id, (int)$position];
		}
	}
}
