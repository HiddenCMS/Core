<?php
/**
 * https://neofr.ag
 * @author: Michael BILCOT <michael.bilcot@neofr.ag>
 */

$menu_id = isset($model['menu_id']) ? (int)$model['menu_id'] : 0;

$this	->rule($this->form_text('title')
					->title($this->lang('Title'))
					->required()
		)
		->rule($this->form_text('name')
					->title($this->lang('Path'))
					->check(function($post, $data) use ($menu_id){
						$name = url_title(!empty($data['name']) ? $data['name'] : (!empty($data['title']) ? $data['title'] : ''));

						if ($name === '')
						{
							return $this->lang('Please enter a valid title');
						}

						$query = HiddenCMS()->db	->from('menus')
												->where('name', $name);

						if ($menu_id)
						{
							$query->where('menu_id <>', $menu_id);
						}

						if (!$query->empty())
						{
							return $this->lang('Path already in use');
						}
					})
		);
