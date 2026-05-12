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

	private function admin_controller()
	{
		return $this->module->controller('admin');
	}

	public function add()
	{
		if (!$this->is_authorized('add_menus'))
		{
			$this->error->unauthorized();
		}

		return $this->admin_controller()->add();
	}

	public function _edit($menu_id, $name)
	{
		if (!$this->is_authorized('modify_menus'))
		{
			$this->error->unauthorized();
		}

		if (!($menu = $this->menu_model()->check_menu((int)$menu_id, (string)$name)))
		{
			$this->error();
		}

		return $this->admin_controller()->_edit($menu['menu_id'], $menu['name'], $menu['title']);
	}

	public function delete($menu_id, $name)
	{
		if (!$this->is_authorized('delete_menus'))
		{
			$this->error->unauthorized();
		}

		if (!($menu = $this->menu_model()->check_menu((int)$menu_id, (string)$name)))
		{
			$this->error();
		}

		return $this->admin_controller()->delete($menu['menu_id'], $menu['title']);
	}

	public function _items_add($menu_id, $name)
	{
		if (!$this->is_authorized('modify_menus'))
		{
			$this->error->unauthorized();
		}

		if (!($menu = $this->menu_model()->check_menu((int)$menu_id, (string)$name)))
		{
			$this->error();
		}

		return $this->admin_controller()->_items_add($menu);
	}

	public function _items_edit($menu_id, $name, $item_id)
	{
		if (!$this->is_authorized('modify_menus'))
		{
			$this->error->unauthorized();
		}

		if (!($menu = $this->menu_model()->check_menu((int)$menu_id, (string)$name)))
		{
			$this->error();
		}

		if (!($item = $this->menu_model()->check_item((int)$item_id, (int)$menu['menu_id'])))
		{
			$this->error();
		}

		return $this->admin_controller()->_items_edit($menu, $item);
	}

	public function _items_delete($menu_id, $name, $item_id, $title)
	{
		if (!$this->is_authorized('delete_menus'))
		{
			$this->error->unauthorized();
		}

		if (!($menu = $this->menu_model()->check_menu((int)$menu_id, (string)$name)))
		{
			$this->error();
		}

		if (!($item = $this->menu_model()->check_item((int)$item_id, (int)$menu['menu_id'], (string)$title)))
		{
			$this->error();
		}

		return $this->admin_controller()->_items_delete($menu, $item['item_id'], $item['title']);
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
