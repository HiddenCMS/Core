<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
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
			'author'      => 'MichaÃ«l BILCOT & JÃ©rÃ©my VALENTIN <contact@HiddenCMS.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'zones'       => [$this->lang('Contenu'), $this->lang('pre_content'), $this->lang('post_content'), $this->lang('header'), $this->lang('Haut'), $this->lang('footer')]
		];
	}

	public function __init()
	{
		$this	->css('https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.4/dist/semantic.min.css')
				->css('fonts/open-sans')
				->css('fonts/titillium-web')
				->css('icons/Pe-icon-7-stroke')
				->css('icons/fontawesome.min')
				->css('access-modal')
				->css('fomantic-admin')
				->js('jquery-3.2.1.min')
				->js('https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.4/dist/semantic.min.js')
				->js('modal')
				->js('form')
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

		$appearance_links = array_values(array_filter([
			$customize->__toArray(),
			[
				'title'  => 'Themes',
				'icon'   => 'far fa-image',
				'access' => $this->user->admin,
				'url'    => 'admin/addons/themes'
			],
			[
				'title'  => 'Modules',
				'icon'   => 'fas fa-cube',
				'access' => $this->user->admin,
				'url'    => 'admin/addons/modules'
			],
			[
				'title'  => 'Widgets',
				'icon'   => 'fas fa-cubes',
				'access' => $this->user->admin,
				'url'    => 'admin/addons/widgets'
			],
			[
				'title'  => 'Live Editor',
				'icon'   => 'fas fa-desktop',
				'access' => $this->user->admin,
				'url'    => 'admin/live-editor'
			]
		]));

		$administration_links = array_values(array_filter([
			[
				'title'  => 'Parametres',
				'icon'   => 'fas fa-cogs',
				'access' => $this->user->admin,
				'url'    => 'admin/settings'
			],
			[
				'title'  => 'Membres / Groupes',
				'icon'   => 'fas fa-users',
				'access' => $this->user->admin,
				'url'    => 'admin/user'
			],
			[
				'title'  => 'Sessions',
				'icon'   => 'fas fa-globe',
				'access' => $this->user->admin,
				'url'    => 'admin/user/sessions'
			],
			[
				'title'  => 'Permissions',
				'icon'   => 'fas fa-unlock-alt',
				'access' => $this->user->admin,
				'url'    => 'admin/access'
			],
			[
				'title'  => 'Statistiques',
				'icon'   => 'far fa-chart-bar',
				'access' => $this->user->admin,
				'url'    => 'admin/statistics'
			]
		]));

		$updates_count = 0;

		if ($this->user->admin)
		{
			try
			{
				$updates_status = $this->core_updater->status();
				$updates_count = (!empty($updates_status['core']['available']) ? 1 : 0) + count(isset($updates_status['addons']) && is_array($updates_status['addons']) ? $updates_status['addons'] : []);
			}
			catch (\Throwable $e)
			{
				$updates_count = 0;
			}
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
					'title'     => 'Mises à jour',
					'icon'      => 'fas fa-cloud-download-alt',
					'access'    => $this->user->admin,
					'url'       => 'admin/settings/updates',
					'indicator' => $updates_count ?: NULL
				],
				[
					'title' => 'Contenu',
					'icon'  => 'fas fa-edit',
					'url'   => $content_submenu['default'] ?: NULL
				],
				[
					'title' => 'Gaming',
					'icon'  => 'fas fa-gamepad',
					'url'   => $content_submenu['gaming'] ?: NULL
				],
				[
					'title'  => 'Apparence',
					'icon'   => 'fas fa-paint-brush',
					'access' => $this->user->admin,
					'url'    => $appearance_links ?: NULL
				],
				[
					'title'  => 'Administration',
					'icon'   => 'fas fa-tools',
					'access' => $this->user->admin,
					'url'    => $administration_links ?: NULL
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
