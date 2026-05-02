<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Addons\Authenticator;

use HB\HiddenCMS\Loadables\Addon;

class Authenticator extends Addon
{
	protected function __info()
	{
		return [
			'title'   => 'Authentificateur',
			'icon'    => 'fas fa-sign-in-alt',
			'version' => '1.0',
			'depends' => [
				'HiddenCMS' => 'Alpha 0.2'
			]
		];
	}
}


