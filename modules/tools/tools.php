<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Modules\Tools;

use HD\Hidden\Addons\Module;

class Tools extends Module
{
	protected function __info()
	{
		return [
			'title'       => 'Tools',
			'description' => '',
			'icon'        => 'fas fa-wrench',
			'link'        => 'https://neofr.ag',
			'author'      => 'Michaël BILCOT & Jérémy VALENTIN <contact@hidden.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>'
		];
	}
}
