<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Layouts;

use HB\HiddenCMS\Addons\Module;

class Layouts extends Module
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('Layouts'),
			'description' => $this->lang('Outlines et composition globale des pages'),
			'icon'        => 'fas fa-layer-group',
			'link'        => 'https://hiddencms.dev',
			'author'      => 'HiddenCMS',
			'license'     => 'LGPLv3',
			'admin'       => TRUE,
			'version'     => '1.0.0',
			'routes'      => [
				'admin{pages}'            => 'index',
				'admin/add'               => 'add',
				'admin/{id}/{url_title*}' => '_edit'
			]
		];
	}
}
