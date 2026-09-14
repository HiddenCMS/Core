<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

$this	->compact()
		->rule($this->form_text('login')
					->title($this->module('user')->model('fields')->login_label())
					->required()
					->check(function($data){
						if (!is_string($data['login'] ?? NULL)) return (string)$this->lang('Invalid identifier');
					})
		)
		->rule($this->form_password('password')
					->title((string)$this->lang('Password'))
					->required()
					->check(function($data){
						if (!is_string($data['password'] ?? NULL)) return (string)$this->lang('Invalid password');
					})
		)
		->rule($this->form_checkbox('remember')
					->value(['on'])
					->data([
						'on' => (string)$this->lang('Remember me')
					])
		)
		->success(function($data, $form){
			$user = $this->module('user')->model('fields')->login_user($data['login']);

			//TODO admin123
			if ($user() && $user->password($data['password']))
			{
				if ($this->config->registration_validation && !$user->last_activity_date && !$user->data->get('registration_verified'))
				{
					//Vous devez valider votre inscription, recevoir un nouveau mail de validation
					//TODO
				}
				else
				{
					$this->session->login($user, in_array('on', $data['remember']));
					$this->url->redirect(user_login_destination());
				}
			}
			else
			{
				$form->error((string)$this->lang('Invalid credentials'));
			}
		})
		->submit((string)$this->lang('Sign in'));
