<?php
/**
 * https://neofr.ag
 * @author: Michael BILCOT <michael.bilcot@neofr.ag>
 */

$parent_items = isset($model['parent_items']) && is_array($model['parent_items']) ? $model['parent_items'] : ['' => $this->lang('Aucun (niveau racine)')];
$enabled = array_key_exists('enabled', $model) ? (bool)$model['enabled'] : TRUE;

$this	->rule($this->form_text('title')
					->title($this->lang('Titre'))
					->required()
		)
		->rule($this->form_text('url')
					->title($this->lang('URL'))
					->required()
		)
		->rule($this->form_select('target')
					->title($this->lang('Cible'))
					->data([
						'_parent' => $this->lang('Meme fenetre'),
						'_blank'  => $this->lang('Nouvelle fenetre')
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
						'1' => $this->lang('Lien active')
					])
					->value($enabled)
		);
