<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace NF\Modules\Addons\Controllers\Addons;

use HB\HiddenCMS\Loadables\Controller;

class Widget extends Controller
{
	public $__label = ['Widgets', 'Widget', 'fas fa-cube', 'warning'];

	public function __actions()
	{
		return $this->array
					->set('enable', ['Activer', 'fas fa-check', 'success', TRUE, function($addon){
						return $addon->is_deactivatable() && !$addon->is_enabled();
					}])
					->set('disable', ['DÃ©sactiver', 'fas fa-times', 'muted', TRUE, function($addon){
						return $addon->is_deactivatable() && $addon->is_enabled();
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
}


