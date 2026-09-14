<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

$this	->rule($this->form_password('password')
					->title((string)$this->lang('Password'))
					->required()
		)
		->rule($this->form_password('password_confirm')
					->title((string)$this->lang('Confirm password'))
					->required()
					->check(function($data){
						if ($data['password'] && $data['password'] !== $data['password_confirm'])
						{
							return (string)$this->lang('Passwords do not match');
						}
					})
		);
