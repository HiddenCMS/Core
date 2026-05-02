<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Addons\Controllers\Addons;

use HB\HiddenCMS\Loadables\Controller;

class Theme extends Controller
{
	public $__label = ['ThÃ¨mes', 'ThÃ¨me', 'fas fa-tint', 'success'];

	public function __actions()
	{
		return $this->array
					->set('enable',    ['Activer', 'fas fa-check', 'success', TRUE, function($addon){
						return $addon->info()->name != 'admin' && !$addon->is_enabled();
					}])
					->set('customize', ['Personaliser', 'fas fa-paint-brush', 'info', FALSE, function($addon){
						return $addon->info()->name != 'admin' && @$addon->controller('admin');
					}])
					->set('reset',     ['RÃ©installer par dÃ©faut', 'fas fa-sync', 'warning', TRUE, function($addon){
						return $addon->info()->name != 'admin';
					}]);
	}

	public function enable($addon)
	{
		$this->config('default_theme', $addon->info()->name);

		notify($this->lang('<b>%s</b> activÃ©', $addon->info()->title));

		refresh();
	}

	public function customize($theme, $controller)
	{
		$controller	->title($theme->info()->title)
					->subtitle('Personnalisation du thÃ¨me')
					->icon('fas fa-paint-brush')
					->add_action($this->button('RÃ©installer par dÃ©faut', 'fas fa-sync', 'warning')->modal($this->reset($theme)));

		return $theme->controller('admin')->index();
	}

	public function reset($theme)
	{
		return $this->modal('RÃ©installer par dÃ©faut', 'fas fa-sync')
					->body($this->lang('ÃŠtes-vous sÃ»r(e) de vouloir rÃ©installer le thÃ¨me <b>%s</b> ?<br />Toutes les dispositions et configurations de widgets seront perdues.', $theme->info()->title))
					->submit('RÃ©installer', 'warning')
					->cancel()
					->callback(function() use ($theme){
						$theme->reset();
						notify($this->lang('ThÃ¨me %s rÃ©installÃ© par dÃ©faut', $theme->info()->title));
						refresh();
					});
	}
}


