<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Addons\Authenticator_Linkedin;

use HD\Hidden\Addons\Authenticator;

class Authenticator_Linkedin extends Authenticator
{
	protected function __info()
	{
		return [
			'title'   => 'LinkedIn',
			'icon'    => 'fab fa-linkedin-in',
			'color'   => '#0077B5',
			'help'    => 'https://www.linkedin.com/secure/developer?newapp=',
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
					'username' => $data->firstname.' '.$data->lastname,
					'avatar'   => $data->picture
				];
			};
		}
	}
}
