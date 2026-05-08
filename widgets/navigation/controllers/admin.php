<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Widgets\Navigation\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Widget as Controller_Widget;

class Admin extends Controller_Widget
{
	public function index($settings = [])
	{
		return $this->view('admin', $settings + [
			'menus' => $this->menus()
		]);
	}

	public function vertical($settings = [])
	{
		return $this->view('admin', $settings + [
			'menus' => $this->menus()
		]);
	}

	private function menus()
	{
		if (($module = @HiddenCMS()->module('menu')) && $module->is_enabled())
		{
			return $module->model2('menu')->get_menu_choices();
		}

		return [];
	}
}


