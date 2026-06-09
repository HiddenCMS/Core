<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Access;

use HB\HiddenCMS\Addons\Module;

class Access extends Module
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('Permissions'),
			'description' => '',
			'icon'        => 'fas fa-unlock-alt',
			'link'        => 'https://neofr.ag',
			'author'      => 'Michaël BILCOT & Jérémy VALENTIN <contact@HiddenCMS.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'admin'       => FALSE,
			'routes'      => [
				'admin/ajax/edit/{url_title}/{url_title}' => '_edit',
				'admin/edit/{url_title*}'  => '_edit',
				'admin/([a-z0-9-]*?){pages}' => 'index'
			]
		];
	}
}


