<?php
/**
 * https://neofr.ag
 * @author: Michael BILCOT <michael.bilcot@neofr.ag>
 */

$outline_id = isset($model['outline_id']) ? (int)$model['outline_id'] : 0;
$themes = isset($model['themes']) && is_array($model['themes']) ? $model['themes'] : [];
$reserved_routes = isset($model['reserved_routes']) && is_array($model['reserved_routes']) ? $model['reserved_routes'] : [];
$selected_reserved_routes = isset($model['selected_reserved_routes']) && is_array($model['selected_reserved_routes']) ? $model['selected_reserved_routes'] : [];
$enabled_default = array_key_exists('enabled', $model) ? (bool)$model['enabled'] : TRUE;
$breadcrumb_default = array_key_exists('breadcrumb', $model) ? (bool)$model['breadcrumb'] : TRUE;
$base_default = !empty($model['base']);

$this	->rule($this->form_text('title')
					->title($this->lang('Title'))
					->required()
		)
		->rule($this->form_text('name')
					->title($this->lang('Internal name'))
					->check(function($post, $data) use ($outline_id){
						$name = url_title(!empty($data['name']) ? $data['name'] : (!empty($data['title']) ? $data['title'] : ''));

						if ($name === '')
						{
							return $this->lang('Please enter a valid title');
						}

						if ($this->model2('outline')->name_exists($name, $outline_id))
						{
							return $this->lang('Internal name already in use');
						}
					})
		)
		->rule($this->form_select('theme')
					->title($this->lang('Theme'))
					->data($themes)
					->required()
		)
		->rule($this->form_select('reserved_routes')
					->title($this->lang('Reserved routes'))
					->info($this->lang('You can target an entire module or one of its pages. A targeted page takes priority over the entire module.'))
					->data($reserved_routes)
					->value($selected_reserved_routes)
					->multiple()
					->search(0)
		)
		->rule($this->form_checkbox('base')
					->data([
						'on' => $this->lang('Default outline')
					])
					->value($base_default ? ['on'] : [])
		)
		->rule($this->form_checkbox('breadcrumb')
					->data([
						'on' => $this->lang('Show breadcrumbs')
					])
					->value($breadcrumb_default ? ['on'] : [])
		)
		->rule($this->form_checkbox('enabled')
					->data([
						'on' => $this->lang('Enable this outline')
					])
					->value($enabled_default ? ['on'] : [])
		);
