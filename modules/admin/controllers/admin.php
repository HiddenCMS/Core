<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Admin\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Admin extends Controller_Module
{
	public function index()
	{
		if (!$this->user->admin) return $this->error->unauthorized();
		$this->title($this->lang('Tableau de bord'))->css('dashboard');
		return $this->view('dashboard', ['dashboard' => $this->model('dashboard')->data()]);
	}

	public function help($module_name, $method)
	{
		if (($module = $this->module($module_name)) && ($help = @$module->controller('admin_help')) && $help->has_method($method))
		{
			$this->ajax();
			return call_user_func_array([$help, $method]);
		}
	}
}


