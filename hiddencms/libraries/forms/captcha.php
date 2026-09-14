<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries\Forms;

class Captcha extends Labelable
{
	protected $_color;
	protected $_compact;
	protected $_session;
	protected $_always;
	protected $_action;

	public function __invoke($name = '', $always = FALSE, $action = 'submit')
	{
		if (!$this->config->captcha_public_key || !$this->config->captcha_private_key)
		{
			return;
		}

		$this->_always = (bool)$always;
		$this->_action = preg_replace('/[^a-zA-Z0-9_\/]/', '_', (string)$action) ?: 'submit';
		$this->__id();

		$this->_check[] = function($post){
			$validated = !$this->_always && ($this->_session = $this->session('captcha', $this->__id()));

			if (($this->_always || !$this->user()) && !$validated)
			{
				if (!empty($post[$this->_name]))
				{
					$result = $this	->network('https://www.google.com/recaptcha/api/siteverify')
									->post([
										'secret'   => $this->config->captcha_private_key,
										'response' => $post[$this->_name],
										'remoteip' => $_SERVER['REMOTE_ADDR']
									]);

					if ($result === FALSE)
					{
						$this->_errors[] = (string)$this->lang('Server error');
					}
					else if (!empty($result->success) &&
						isset($result->action, $result->score) &&
						$result->action === $this->_action &&
						(float)$result->score >= $this->score_threshold())
					{
						if (!$this->_always)
						{
							$this->_session = $this->session->set('captcha', $this->__id(), TRUE);
						}

						return FALSE;
					}
				}

				$this->_errors[] = (string)$this->lang('Anti-bot verification failed');
			}

			return FALSE;
		};

		$this->_template[] = function(&$input){
			if (($this->_always || !$this->user()) && ($this->_always || !$this->_session))
			{
				$this->js('captcha');

				$input = parent	::html('input', TRUE)
								->attr('type', 'hidden')
								->attr('class', 'recaptcha-v3-token')
								->attr('name', 'g-recaptcha-response')
								->attr('data-recaptcha-action', $this->_action);
			}

			return FALSE;
		};

		return parent::__invoke('g-recaptcha-response');
	}

	public function __toString()
	{
		$form = $this->_form;
		$this->_form = NULL;
		$html = parent::__toString();
		$this->_form = $form;

		return $html;
	}

	private function score_threshold()
	{
		$threshold = isset($this->config->captcha_score_threshold) && is_numeric($this->config->captcha_score_threshold)
			? (float)$this->config->captcha_score_threshold
			: 0.5;
		return max(0, min(1, $threshold));
	}

	public function dark()
	{
		$this->_color = 'dark';
		return $this;
	}

	public function compact()
	{
		$this->_compact = TRUE;
		return $this;
	}
}
