<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

$this	->rule($this->form_text('username')
					->title('Pseudo')
					->required()
					->check(function($data){
						if ($data['username'] && !$this->db()->from('user')->where('username', $data['username'])->where('deleted', FALSE)->where_if($this->_values, 'id <>', $this->_values->id)->empty())
						{
							return 'Pseudo déjà utilisé';
						}
					})
		);
