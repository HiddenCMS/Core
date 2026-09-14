<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

$this->rule($this->form_text('subject')
				->title((string)$this->lang('Your subject'))
				->required())
	->rule_if(!$this->user->email, $this->form_email('email')
										->title((string)$this->lang('Your email address'))
										->required())
	->rule($this->form_textarea('message')
				->title((string)$this->lang('Your message'))
				->required())
	->captcha(TRUE, 'contact')
	->submit((string)$this->lang('Send'))
	->success(function($data, $form){
		$sent = $this	->anti_flood()
						->email
						->from($this->user->email ?: $data['email'])
						->to($this->config->contact)
						->subject($data['subject'])
						->message(function() use ($data){
							return [
								'content' => nl2br(strtolink($data['message'])).($this->user() ? '<br /><br />'.$this->user->view('profile') : '')
							];
						})
						->send();

		if ($sent)
		{
			notify((string)$this->lang('Message sent'));
			$this->modal->dispose();
		}
		else
		{
			$form->error((string)$this->lang('An error occurred while sending the message'));
		}
	});
