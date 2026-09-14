<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Addons\Controllers\Addons;

use HB\HiddenCMS\Loadables\Controller;

class Widget extends Controller
{
	public $__label = ['Widgets', 'Widget', 'fas fa-cube', 'warning'];

	public function __actions()
	{
		return $this->array
					->set('enable', [(string)$this->lang('Enable'), 'fas fa-check', 'success', TRUE, function($addon){
						return $addon->is_deactivatable() && !$addon->is_enabled();
					}])
					->set('disable', [(string)$this->lang('Disable'), 'fas fa-times', 'muted', TRUE, function($addon){
						return $addon->is_deactivatable() && $addon->is_enabled();
					}]);
	}

	public function enable($addon)
	{
		$addon->__addon->set('data', $addon->__addon->data->set('enabled', TRUE))->update();

		notify($this->lang('<b>%s</b> enabled', $addon->info()->title));

		refresh();
	}

	public function disable($addon)
	{
		$addon->__addon->set('data', $addon->__addon->data->set('enabled', FALSE))->update();

		notify($this->lang('<b>%s</b> disabled', $addon->info()->title));

		refresh();
	}
}


