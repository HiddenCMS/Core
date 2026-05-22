<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Themes\Admin;

use HB\HiddenCMS\Addons\Theme;

class Admin extends Theme
{
	public $data;

	protected function __info()
	{
		return [
			'title'       => 'Administration',
			'description' => 'Panel d\'administration',
			'link'        => 'https://neofr.ag',
			'author'      => 'Michaël BILCOT & Jérémy VALENTIN <contact@HiddenCMS.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'zones'       => [$this->lang('Contenu'), $this->lang('pre_content'), $this->lang('post_content'), $this->lang('header'), $this->lang('Haut'), $this->lang('footer')]
		];
	}

	public function __init()
	{
		if ($this->config->update_callback)
		{
			$this->config('update_callback', '');

			if ($patch = @HiddenCMS()->install($this->config->update_callback))
			{
				if (method_exists($patch, 'post'))
				{
					$patch->post();
				}

				unlink('HiddenCMS/install/'.$this->config->update_callback.'.php');
			}

			refresh();
		}

		$this	->css('fonts/open-sans')
				->css('fonts/titillium-web')
				->css('icons/Pe-icon-7-stroke')
				->css('icons/fontawesome.min')
				->css('https://cdn.jsdelivr.net/npm/bulma@1.0.4/css/bulma.min.css')
				->css('modern')
				->js('jquery-3.2.1.min')
				->js('bootstrap-notify.min')
				->js('modal')
				->js('notify')
				->js('sidebar');

		$this->data = $this->array;

		$content_submenu = [
			'default' => [],
			'gaming'  => []
		];

		foreach (HiddenCMS()->model2('addon')->get('module') as $module)
		{
			if ($module->is_enabled() && $module->is_administrable($category) && $category != 'none' && $module->is_authorized())
			{
				$content_submenu[isset($content_submenu[$category]) ? $category : 'default'][] = [
					'title' => (string)$module->info()->title,
					'icon'  => $module->info()->icon,
					'url'   => 'admin/'.$module->info()->name
				];
			}
		}

		array_walk($content_submenu, function(&$a){
			array_natsort($a, function($a){
				return $a['title'];
			});
		});

		$customize = $this->array();
		$theme     = HiddenCMS()->model2('addon')->get('theme', $this->config->default_theme, FALSE);

		if (@$theme->addon()->controller('admin'))
		{
			$customize	->set('title',  'Apparence')
						->set('icon',   'fas fa-paint-brush')
						->set('access', $this->user->admin)
						->set('url',   'admin/addons/customize/'.$theme->url());
		}

		$this->data->set('sidebar', [
			'panel' => FALSE,
			'links' => array_filter([
				[
					'title' => 'Tableau de bord',
					'icon'  => 'fas fa-tachometer-alt',
					'url'   => 'admin'
				],
				[
					'title'  => 'Paramètres',
					'icon'   => 'fas fa-cogs',
					'access' => $this->user->admin,
					'url'    => 'admin/settings'
				],
				[
					'title' => 'Contenu',
					'icon'  => 'fas fa-edit',
					'url'   => $content_submenu['default']
				],
				[
					'title' => 'Gaming',
					'icon'  => 'fas fa-gamepad',
					'url'   => $content_submenu['gaming']
				],
				[
					'title' => 'Utilisateurs',
					'icon'  => 'fas fa-users',
					'url'   => [
						['title' => 'Membres / Groupes',      'icon'  => 'fas fa-users',        'access' => $this->user->admin, 'url' => 'admin/user'],
						['title' => 'Sessions',               'icon'  => 'fas fa-globe',        'access' => $this->user->admin, 'url' => 'admin/user/sessions'],
						['title' => 'Permissions',            'icon'  => 'fas fa-unlock-alt',   'access' => $this->user->admin, 'url' => 'admin/access'],
						//['title' => 'Bannissement',           'icon'  => 'fas fa-bomb',         'access' => $this->user->admin, 'url' => 'admin/user/ban']
					]
				],
				[
					'title'  => 'Thèmes & addons',
					'icon'   => 'fas fa-puzzle-piece',
					'access' => $this->user->admin,
					'url'   => [
						['title' => 'Thèmes',      	'icon'  => 'far fa-image',        'access' => $this->user->admin, 'url' => 'admin/addons/themes'],
						['title' => 'Modules',    	'icon'  => 'fas fa-cube',        'access' => $this->user->admin, 'url' => 'admin/addons/modules'],
						['title' => 'Widgets',     	'icon'  => 'fas fa-cubes',   'access' => $this->user->admin, 'url' => 'admin/addons/widgets']
					]
				],
				$customize->__toArray(),
				[
					'title' => 'Live Editor',
					'icon'  => 'fas fa-desktop',
					'access' => $this->user->admin,
					'url'   => 'admin/live-editor'
				],
				[
					'title' => 'Statistiques',
					'icon'  => 'far fa-chart-bar',
					'access' => $this->user->admin,
					'url'    => 'admin/statistics'
				]
			])
		]);
	}

	public function styles_row()
	{
		//Nothing to do
	}

	public function styles_widget()
	{
		//Nothing to do
	}

}


