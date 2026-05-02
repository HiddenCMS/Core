<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Members;

use HB\HiddenCMS\Addons\Module;

class Members extends Module
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('Liste des membres'),
			'description' => '',
			'icon'        => 'fas fa-users',
			'link'        => 'https://neofr.ag',
			'author'      => 'Michaël BILCOT & Jérémy VALENTIN <contact@HiddenCMS.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'front'       => TRUE,
			'version'     => '1.0',
			'depends'     => [
				'HiddenCMS' => 'Alpha 0.2'
			],
			'routes'      => [
				'{pages}'                                   => 'index',
				'group/(admins|members){pages}'             => '_group',
				'group/{url_title}-{id}/{url_title}{pages}' => '_group',
				'group/{id}/{url_title}{pages}'             => '_group'
			]
		];
	}
}


