<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Addons\Authenticator_Twitter;

use HD\Hidden\Addons\Authenticator;

class Authenticator_Twitter extends Authenticator
{
	protected function __info()
	{
		return [
			'title'   => 'Twitter',
			'icon'    => 'fab fa-twitter',
			'color'   => '#1da1f2',
			'help'    => 'https://apps.twitter.com/app/new',
			'version' => '1.0',
			'depends' => [
				'addon/authenticator' => '1.0'
			]
		];
	}

	public function data(&$params = [])
	{
		if (!empty($_GET['oauth_token']))
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
