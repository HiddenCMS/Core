<?php
/**
 * https://neofr.ag
 * @author: Michael BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Menu\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module_Checker;

class Admin_Checker extends Module_Checker
{
	private function menu_model()
	{
		return $this->module->model2('menu');
	}

	public function index($page = '')
	{
		return [$this->module->pagination->get_data($this->menu_model()->get_menus(), $page)];
	}

	public function add()
	{
		if (!$this->is_authorized('add_menus'))
		{
			$this->error->unauthorized();
		}

		return [];
	}

	public function _edit($menu_id, $name)
	{
		if (!$this->is_authorized('modify_menus'))
		{
			$this->error->unauthorized();
		}

		if ($menu = $this->menu_model()->check_menu($menu_id, $name))
		{
			return $menu;
		}
	}

	public function delete($menu_id, $name)
	{
		if (!$this->is_authorized('delete_menus'))
		{
			$this->error->unauthorized();
		}

		$this->ajax();

		if ($menu = $this->menu_model()->check_menu($menu_id, $name))
		{
			return [$menu['menu_id'], $menu['title']];
		}
	}

	public function _items($menu_id, $name, $page = '')
	{
		if (!$this->is_authorized('modify_menus'))
		{
			$this->error->unauthorized();
		}

		if ($menu = $this->menu_model()->check_menu($menu_id, $name))
		{
			return [$menu, $this->menu_model()->get_menu_items($menu_id)];
		}
	}

	public function _items_add($menu_id, $name)
	{
		if (!$this->is_authorized('modify_menus'))
		{
			$this->error->unauthorized();
		}

		if ($menu = $this->menu_model()->check_menu($menu_id, $name))
		{
			return [$menu];
		}
	}

	public function _items_edit($menu_id, $name, $item_id)
	{
		if (!$this->is_authorized('modify_menus'))
		{
			$this->error->unauthorized();
		}

		if (($menu = $this->menu_model()->check_menu($menu_id, $name)) && ($item = $this->menu_model()->check_item($item_id, $menu_id)))
		{
			return [$menu, $item];
		}
	}

	public function _items_delete($menu_id, $name, $item_id, $title)
	{
		if (!$this->is_authorized('delete_menus'))
		{
			$this->error->unauthorized();
		}

		$this->ajax();

		if (($menu = $this->menu_model()->check_menu($menu_id, $name)) && ($item = $this->menu_model()->check_item($item_id, $menu_id, $title)))
		{
			return [$menu, $item['item_id'], $item['title']];
		}
	}
}
