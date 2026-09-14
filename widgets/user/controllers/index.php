<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Widgets\User\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Widget as Controller_Widget;

class Index extends Controller_Widget
{
	public function index($config = [])
	{
		if ($this->user())
		{
			$this->css('user');

			return $this->panel()
						->heading($this->lang('Member area'))
						->body($this->view('logged', [
							'username' => $this->user->username
						]), FALSE)
						->footer('<a href="'.url('user/logout').'">'.icon('fas fa-times').' '.$this->lang('Logout').'</a>');
		}
		else
		{
			if ($authenticators = HB()->model2('addon')->get('authenticator')->filter('is_enabled')->__toArray())
			{
				$this	->css('auth')
						->css('auth_mini');
			}

			return $this->module('user')
						->form2('login')
						->button_prepend_if($this->config->registration_status, $this->button()
																				->title('Create an account')
																				->color('secondary')
																				->url('user/register')
						)
						->button_prepend($this	->button()
												->title((string)$this->lang('Forgot your password?'))
												->color('link')
												->url('user/lost-password')
						)
						->panel()
						->title($this->lang('Member area').($authenticators ? '<div class="float-right">'.implode($authenticators).'</div>' : ''));
		}
	}

	public function index_mini($config = [])
	{
		return $this->view('index_mini', $config);
	}

	public function messages_inbox($config = [])
	{
		if ($this->user())
		{
			return $this->panel()
						->heading($this->lang('Private messages'), 'fas fa-envelope')
						->body($this->view('messages_inbox', [
							'messages' => array_slice($this->module('user')->model('messages')->get_messages_inbox(), 0, 5)
						]), FALSE)
						->footer('<a class="btn btn-secondary" href="'.url('user/messages').'">'.icon('fas fa-inbox').' '.$this->lang('Inbox').'</a> <a class="btn btn-primary" href="'.url('user/messages/compose').'">'.icon('fas fa-edit').' '.$this->lang('Write').'</a>');
		}
	}
}


