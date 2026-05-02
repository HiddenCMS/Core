<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace NF\Modules\Addons;

use HB\HiddenCMS\Addons\Module;

class Addons extends Module
{
	protected function __info()
	{
		return [
			'title'       => 'ThÃ¨mes & Addons',
			'description' => '',
			'icon'        => 'fas fa-puzzle-piece',
			'link'        => 'https://neofr.ag',
			'author'      => 'MichaÃ«l BILCOT & JÃ©rÃ©my VALENTIN <contact@neofrag.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'admin'       => FALSE,
			'routes'      => [
				'admin/{url_title}/{id}/{url_title}' => '_action'
			]
		];
	}
}


