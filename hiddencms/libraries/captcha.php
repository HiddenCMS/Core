<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries;

use HB\HiddenCMS\Library;

class Captcha extends Library
{
	protected $_action = 'submit';

	public function is_ok()
	{
		return $this->config->captcha_public_key && $this->config->captcha_private_key;
	}

	public function is_valid($action = 'submit')
	{
		$this->_action = preg_replace('/[^a-zA-Z0-9_\/]/', '_', (string)$action) ?: 'submit';

		if ($response = post('g-recaptcha-response'))
		{
			$result = $this->network('https://www.google.com/recaptcha/api/siteverify')->post([
				'secret'   => $this->config->captcha_private_key,
				'response' => $response,
				'remoteip' => $_SERVER['REMOTE_ADDR']
			]);

			$threshold = isset($this->config->captcha_score_threshold) && is_numeric($this->config->captcha_score_threshold)
				? (float)$this->config->captcha_score_threshold
				: 0.5;

			return $result !== FALSE &&
				!empty($result->success) &&
				isset($result->action, $result->score) &&
				$result->action === $this->_action &&
				(float)$result->score >= max(0, min(1, $threshold));
		}

		return FALSE;
	}

	public function display($action = 'submit')
	{
		$this->_action = preg_replace('/[^a-zA-Z0-9_\/]/', '_', (string)$action) ?: 'submit';
		return '<input type="hidden" class="recaptcha-v3-token" name="g-recaptcha-response" data-recaptcha-action="'.$this->_action.'" />';
	}
}


