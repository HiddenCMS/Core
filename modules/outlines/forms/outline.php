<?php
/**
 * https://neofr.ag
 * @author: Michael BILCOT <michael.bilcot@neofr.ag>
 */

$outline_id = isset($model['outline_id']) ? (int)$model['outline_id'] : 0;
$themes = isset($model['themes']) && is_array($model['themes']) ? $model['themes'] : [];
$enabled_default = array_key_exists('enabled', $model) ? (bool)$model['enabled'] : TRUE;
$base_default = !empty($model['base']);

$this	->rule($this->form_text('title')
					->title($this->lang('Titre'))
					->required()
		)
		->rule($this->form_text('name')
					->title($this->lang('Nom technique'))
					->check(function($post, $data) use ($outline_id){
						$name = url_title(!empty($data['name']) ? $data['name'] : (!empty($data['title']) ? $data['title'] : ''));

						if ($name === '')
						{
							return $this->lang('Veuillez renseigner un titre valide');
						}

						if ($this->model2('outline')->name_exists($name, $outline_id))
						{
							return $this->lang('Nom technique deja utilise');
						}
					})
		)
		->rule($this->form_select('theme')
					->title($this->lang('Theme'))
					->data($themes)
					->required()
		)
		->rule($this->form_checkbox('base')
					->data([
						'on' => $this->lang('Outline de base')
					])
					->value($base_default ? ['on'] : [])
		)
		->rule($this->form_checkbox('enabled')
					->data([
						'on' => $this->lang('Activer cet outline')
					])
					->value($enabled_default ? ['on'] : [])
		);

