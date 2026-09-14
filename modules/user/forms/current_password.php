<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

$this	->rule($this->form_password('password')
					->title('Current password')
					->value('')
					->check(function($data){
						if ($data['password'] && !$this->_values->password($data['password']))
						{
							return (string)$this->lang('Incorrect password');
						}
					})
					->required()
		);
