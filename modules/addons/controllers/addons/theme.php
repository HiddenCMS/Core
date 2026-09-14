<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Addons\Controllers\Addons;

use HB\HiddenCMS\Loadables\Controller;

class Theme extends Controller
{
	public $__label = ['Themes', 'Theme', 'fas fa-tint', 'success'];

	public function __actions()
	{
		return $this->array
					->set('enable',    [(string)$this->lang('Enable'), 'fas fa-check', 'success', TRUE, function($addon){
						return $addon->info()->name != 'admin' && !$addon->is_enabled();
					}])
					->set('customize', [(string)$this->lang('Customize'), 'fas fa-paint-brush', 'info', FALSE, function($addon){
						return $addon->info()->name != 'admin' && @$addon->controller('admin');
					}])
					->set('reset',     [(string)$this->lang('Reset to defaults'), 'fas fa-sync', 'warning', TRUE, function($addon){
						return $addon->info()->name != 'admin';
					}]);
	}

	public function enable($addon)
	{
		if ($this->db->from('dispositions')->where('theme', $addon->info()->name)->empty())
		{
			$addon->install();
		}

		$this->db	->where('base', TRUE)
					->update('outlines', [
						'theme' => $addon->info()->name
					]);

		$this->config('default_theme', $addon->info()->name);

		notify($this->lang('<b>%s</b> enabled', $addon->info()->title));

		refresh();
	}

	public function customize($theme, $controller)
	{
		$controller	->title($theme->info()->title)
					->subtitle((string)$this->lang('Theme customization'))
					->icon('fas fa-paint-brush')
					->add_action($this->button((string)$this->lang('Reset to defaults'), 'fas fa-sync', 'warning')->modal($this->reset($theme)));

		return $theme->controller('admin')->index();
	}

	public function reset($theme)
	{
		return $this->modal((string)$this->lang('Reset to defaults'), 'fas fa-sync')
					->body($this->lang('Are you sure you want to reinstall the <b>%s</b> theme?<br />All widget layouts and configurations will be lost.', $theme->info()->title))
					->submit((string)$this->lang('Reinstall'), 'warning')
					->cancel()
					->callback(function() use ($theme){
						$theme->reset();
						notify($this->lang('Theme %s reset to defaults', $theme->info()->title));
						refresh();
					});
	}
}


