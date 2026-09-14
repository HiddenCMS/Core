<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

$this	->rule($this->form_password('password_new')
					->title('New password')
					->size('col-6')
		)
		->rule($this->form_password('password_confirm')
					->title((string)$this->lang('Confirm password'))
					->size('col-6')
					->check(function($data){
						if ($data['password_new'] && $data['password_new'] !== $data['password_confirm'])
						{
							return (string)$this->lang('Passwords do not match');
						}
					})
		);
