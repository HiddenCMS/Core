<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Addons\Authenticator;

use HD\Hidden\Loadables\Addon;

class Authenticator extends Addon
{
	protected function __info()
	{
		return [
			'title'   => 'Authentificateur',
			'icon'    => 'fas fa-sign-in-alt',
			'version' => '1.0',
			'depends' => [
				'hidden' => '0.0.1'
			]
		];
	}
}
