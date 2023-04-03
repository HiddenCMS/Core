<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Addons\Authenticator_Github;

use HD\Hidden\Addons\Authenticator;

class Authenticator_Github extends Authenticator
{
	protected function __info()
	{
		return [
			'title'   => 'GitHub',
			'icon'    => 'fab fa-github',
			'color'   => '#24292e',
			'help'    => 'https://github.com/settings/applications/new',
			'version' => '1.0',
			'depends' => [
				'addon/authenticator' => '1.0'
			]
		];
	}

	public function data(&$params = [])
	{
		if (!empty($_GET['code']) && !empty($_GET['state']))
		{
			$params = $_GET;

			return function($data){
				return [
					'id'       => $data->id,
					'username' => $data->username,
					'avatar'   => $data->pictureURL
				];
			};
		}
	}
}
