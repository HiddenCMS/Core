<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Addons\Controllers\Addons;

use HB\HiddenCMS\Loadables\Controller;

class Module extends Controller
{
	public $__label = ['Modules', 'Module', 'far fa-sticky-note', 'primary'];

	public function __actions()
	{
		return $this->array
					->set('enable', ['Activer', 'fas fa-check', 'success', TRUE, function($addon){
						return $addon->is_deactivatable() && !$addon->is_enabled();
					}])
					->set('disable', ['DÃ©sactiver', 'fas fa-times', 'muted', TRUE, function($addon){
						return $addon->is_deactivatable() && $addon->is_enabled();
					}])
					->set('settings', ['Configuration', 'fas fa-wrench', 'warning', TRUE, function($addon){
						return isset($addon->info()->settings);
					}])
					->set('access', ['Permissions', 'fas fa-unlock-alt', 'success', FALSE, function($addon){
						return $addon->get_permissions('default');
					}]);
	}

	public function enable($addon)
	{
		$addon->__addon->set('data', $addon->__addon->data->set('enabled', TRUE))->update();

		notify($this->lang('<b>%s</b> activÃ©', $addon->info()->title));

		refresh();
	}

	public function disable($addon)
	{
		$addon->__addon->set('data', $addon->__addon->data->set('enabled', FALSE))->update();

		notify($this->lang('<b>%s</b> dÃ©sactivÃ©', $addon->info()->title));

		refresh();
	}

	public function settings($addon)
	{
		return call_user_func($addon->info()->settings)	->modal($addon->info()->title, 'fas fa-wrench')
														->cancel();
	}

	public function access($addon)
	{
		redirect('admin/access/edit/'.$addon->info()->name);
	}
}


