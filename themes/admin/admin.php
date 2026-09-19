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
			'description' => $this->lang('Administration panel'),
			'link'        => 'https://neofr.ag',
			'author'      => 'MichaÃ«l BILCOT & JÃ©rÃ©my VALENTIN <contact@HiddenCMS.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'zones'       => [$this->lang('Content'), $this->lang('Before content'), $this->lang('After content'), $this->lang('Header'), $this->lang('Top'), $this->lang('Footer')]
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
				->css('sidebar')
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
		$relocated_links = [];

		foreach (HiddenCMS()->model2('addon')->get('module') as $module)
		{
			if ($module->is_enabled() && $module->is_administrable($category) && $category != 'none' && $module->is_authorized())
			{
				$link = [
					'title' => (string)$module->info()->title,
					'icon'  => $module->info()->icon,
					'url'   => 'admin/'.$module->info()->name
				];

				if (in_array($module->info()->name, ['files', 'menu', 'outlines'], TRUE))
				{
					$relocated_links[$module->info()->name] = $link;
				}
				else
				{
					$content_submenu[isset($content_submenu[$category]) ? $category : 'default'][] = $link;
				}
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
			$customize	->set('title',  (string)$this->lang('Appearance'))
						->set('icon',   'fas fa-paint-brush')
						->set('access', $this->user->admin)
						->set('url',   'admin/addons/customize/'.$theme->url());
		}

		$configuration_links = array_values(array_filter([
			$customize->__toArray(),
			[
				'title'  => (string)$this->lang('Settings'),
				'icon'   => 'fas fa-cogs',
				'access' => $this->user->admin,
				'url'    => 'admin/settings'
			],
			isset($relocated_links['menu']) ? $relocated_links['menu'] : [],
			isset($relocated_links['outlines']) ? $relocated_links['outlines'] : [],
			[
				'title'  => $this->lang('Layout builder'),
				'icon'   => 'fas fa-desktop',
				'access' => $this->user->admin,
				'url'    => 'admin/live-editor'
			],
		]));

		$user_links = array_values(array_filter([
			[
				'title'  => (string)$this->lang('Members / Groups'),
				'icon'   => 'fas fa-users',
				'access' => $this->user->admin,
				'url'    => 'admin/user'
			],
			[
				'title'  => 'Sessions',
				'icon'   => 'fas fa-globe',
				'access' => $this->user->admin,
				'url'    => 'admin/user/sessions'
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
					'title' => (string)$this->lang('Dashboard'),
					'icon'  => 'fas fa-tachometer-alt',
					'url'   => 'admin'
				],
				[
					'title' => (string)$this->lang('Content'),
					'icon'  => 'fas fa-edit',
					'url'   => $content_submenu['default'] ?: NULL
				],
				isset($relocated_links['files']) ? $relocated_links['files'] : [],
				$content_submenu['gaming'] ? [
					'title' => 'Gaming',
					'icon'  => 'fas fa-gamepad',
					'url'   => $content_submenu['gaming']
				] : [],
				[
					'title'  => (string)$this->lang('Users'),
					'icon'   => 'fas fa-users',
					'access' => $this->user->admin,
					'url'    => $user_links ?: NULL
				],
				[
					'title'  => (string)$this->lang('Statistics'),
					'icon'   => 'far fa-chart-bar',
					'access' => $this->user->admin,
					'url'    => 'admin/statistics'
				],
				[
					'title'  => (string)$this->lang('Site configuration'),
					'icon'   => 'fas fa-sliders-h',
					'access' => $this->user->admin,
					'url'    => $configuration_links ?: NULL
				],
				[
					'title'  => (string)$this->lang('Themes & Addons'),
					'icon'   => 'fas fa-puzzle-piece',
					'access' => $this->user->admin,
					'url'    => 'admin/addons'
				],
				[
					'title'     => (string)$this->lang('Updates'),
					'icon'      => 'fas fa-cloud-download-alt',
					'access'    => $this->user->admin,
					'url'       => 'admin/settings/updates',
					'indicator' => $updates_count ?: NULL
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
