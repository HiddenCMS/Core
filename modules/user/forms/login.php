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
						if (!is_string($data['login'] ?? NULL)) return 'Identifiant invalide';
					})
		)
		->rule($this->form_password('password')
					->title('Mot de passe')
					->required()
					->check(function($data){
						if (!is_string($data['password'] ?? NULL)) return 'Mot de passe invalide';
					})
		)
		->rule($this->form_checkbox('remember')
					->value(['on'])
					->data([
						'on' => 'Se souvenir de moi'
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
					refresh();
				}
			}
			else
			{
				$form->error('Identifiants invalides');
			}
		})
		->submit('Se connecter');
