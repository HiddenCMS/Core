<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Outlines;

use HB\HiddenCMS\Addons\Module;

class Outlines extends Module
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('Outlines'),
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
				'admin/ajax/{id}/duplicate' => '_duplicate',
				'admin/ajax/{id}/delete'    => '_delete',
				'admin/{id}/{url_title*}' => '_edit'
			]
		];
	}
}
