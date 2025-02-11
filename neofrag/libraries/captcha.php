<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace NF\NeoFrag\Libraries;

use NF\NeoFrag\Library;

class Captcha extends Library
{
	public function is_ok()
	{
		return $this->config->captcha_public_key && $this->config->captcha_private_key;
	}

	public function is_valid()
	{
		if ($response = post('g-recaptcha-response'))
		{
			return !empty($this->network('https://www.google.com/recaptcha/api/siteverify')->get([
				'secret'   => $this->config->captcha_private_key,
				'response' => $response,
				'remoteip' => $_SERVER['REMOTE_ADDR']
			])->success);
		}

		return FALSE;
	}

	public function display()
	{
		return '<div class="g-recaptcha" data-sitekey="'.$this->config->captcha_public_key.'"></div>';
	}
}
