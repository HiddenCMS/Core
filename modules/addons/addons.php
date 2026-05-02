<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Addons;

use HB\HiddenCMS\Addons\Module;

class Addons extends Module
{
	protected function __info()
	{
		return [
			'title'       => 'Thèmes & Addons',
			'description' => '',
			'icon'        => 'fas fa-puzzle-piece',
			'link'        => 'https://neofr.ag',
			'author'      => 'Michaël BILCOT & Jérémy VALENTIN <contact@HiddenCMS.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'admin'       => FALSE,
			'routes'      => [
				'admin/{url_title}/{id}/{url_title}' => '_action'
			]
		];
	}
}


