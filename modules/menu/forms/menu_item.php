<?php
/**
 * https://neofr.ag
 * @author: Michael BILCOT <michael.bilcot@neofr.ag>
 */

$parent_items = isset($model['parent_items']) && is_array($model['parent_items']) ? $model['parent_items'] : ['' => 'Aucun (niveau racine)'];
$enabled = array_key_exists('enabled', $model) ? (bool)$model['enabled'] : TRUE;
$front_urls = isset($model['front_urls']) && is_array($model['front_urls']) ? $model['front_urls'] : [];
$url_mode = !empty($model['url_mode']) ? $model['url_mode'] : 'custom';

$this	->rule($this->form_text('title')
					->title($this->lang('Titre'))
					->required()
		)
		->rule($this->form_select('url_mode')
					->title($this->lang('Type d\'URL'))
					->data([
						'front'  => 'Element front',
						'custom' => 'Lien custom'
					])
					->value($url_mode)
					->required()
		)
		->rule($this->form_select('front_url')
					->title($this->lang('Element front'))
					->data($front_urls)
					->check(function($post, $data){
						if (($data['url_mode'] ?? 'custom') === 'front' && empty($data['front_url']))
						{
							return $this->lang('Veuillez selectionner un element front');
						}
					})
		)
		->rule($this->form_text('url')
					->title($this->lang('Lien custom'))
					->check(function($post, $data){
						if (($data['url_mode'] ?? 'custom') === 'custom' && empty(trim((string)($data['url'] ?? ''))))
						{
							return $this->lang('Veuillez saisir une URL');
						}
					})
		)
		->rule($this->form_select('target')
					->title($this->lang('Cible'))
					->data([
						'_parent' => 'Meme fenetre',
						'_blank'  => 'Nouvelle fenetre'
					])
					->required()
		)
		->rule($this->form_select('parent_id')
					->title($this->lang('Parent'))
					->data($parent_items)
		)
		->rule($this->form_number('position')
					->title($this->lang('Ordre'))
		)
		->rule($this->form_checkbox('enabled')
					->data([
						'1' => 'Lien active'
					])
					->value($enabled)
		);
