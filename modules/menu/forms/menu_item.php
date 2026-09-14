<?php
/**
 * https://neofr.ag
 * @author: Michael BILCOT <michael.bilcot@neofr.ag>
 */

$parent_items = isset($model['parent_items']) && is_array($model['parent_items']) ? $model['parent_items'] : ['' => (string)$this->lang('None (root level)')];
$enabled = array_key_exists('enabled', $model) ? (bool)$model['enabled'] : TRUE;
$front_urls = isset($model['front_urls']) && is_array($model['front_urls']) ? $model['front_urls'] : [];
$current_url = isset($model['url']) ? trim((string)$model['url']) : '';
$is_front_url = ($current_url !== '' && isset($front_urls[$current_url]));
$this	->rule($this->form_text('title')
					->title($this->lang('Title'))
					->required()
		)
		->rule($this->form_select('menu_url_mode')
					->title($this->lang('URL type'))
					->data([
						'front'  => $this->lang('Frontend item'),
						'custom' => $this->lang('Custom link')
					])
					->value($is_front_url ? 'front' : 'custom')
					->size('col-6')
		)
		->rule($this->form_select('target')
					->title($this->lang('Target'))
					->data([
						'_parent' => (string)$this->lang('Same window'),
						'_blank'  => (string)$this->lang('New window')
					])
					->required()
					->size('col-6')
		)
		->rule($this->form_select('menu_front_url')
					->title($this->lang('Frontend item'))
					->placeholder($this->lang('Choose an item'))
					->data($front_urls)
					->value($is_front_url ? $current_url : '')
					->search(0)
		)
		->rule($this->form_text('url')
					->title($this->lang('Link'))
					->required()
					->check(function($post, $data){
						if (empty(trim((string)($data['url'] ?? ''))))
						{
							return $this->lang('Please enter a URL');
						}
					})
		)
		->rule($this->form_select('parent_id')
					->title($this->lang('Parent'))
					->data($parent_items)
		)
		->rule($this->form_checkbox('enabled')
					->size('hb-switch-field')
					->data([
						'1' => (string)$this->lang('Active link')
					])
					->value($enabled)
		);
