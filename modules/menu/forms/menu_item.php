<?php
/**
 * https://neofr.ag
 * @author: Michael BILCOT <michael.bilcot@neofr.ag>
 */

$parent_items = isset($model['parent_items']) && is_array($model['parent_items']) ? $model['parent_items'] : ['' => 'Aucun (niveau racine)'];
$enabled = array_key_exists('enabled', $model) ? (bool)$model['enabled'] : TRUE;
$front_urls = isset($model['front_urls']) && is_array($model['front_urls']) ? $model['front_urls'] : [];
$current_url = isset($model['url']) ? trim((string)$model['url']) : '';
$is_front_url = ($current_url !== '' && isset($front_urls[$current_url]));
$front_options = '';

foreach ($front_urls as $path => $label)
{
	$front_options .= '<option value="'.utf8_htmlentities($path).'"'.($is_front_url && $current_url === (string)$path ? ' selected' : '').'>'.utf8_htmlentities($label).'</option>';
}

$picker_inline = '	<div class="hb-url-picker" data-menu-url-picker>
						<div class="hb-url-picker-title">'.$this->lang('Type d\'URL').'</div>
						<div class="hb-url-picker-modes">
							<label class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="menu_url_mode" value="front"'.($is_front_url ? ' checked' : '').'>
								<span class="form-check-label">'.$this->lang('Element front').'</span>
							</label>
							<label class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="menu_url_mode" value="custom"'.(!$is_front_url ? ' checked' : '').'>
								<span class="form-check-label">'.$this->lang('Lien custom').'</span>
							</label>
						</div>
						<div class="hb-url-picker-front">
							<label class="col-form-label" for="menu-front-url-select">'.$this->lang('Element front').'</label>
							<select class="form-control" id="menu-front-url-select">
								<option value="">'.$this->lang('Choisir un element').'</option>
								'.$front_options.'
							</select>
						</div>
					</div>';

$this	->rule($this->form_text('title')
					->title($this->lang('Titre'))
					->required()
		)
		->rule($this->form_text('url')
					->title($this->lang('Lien'))
					->required()
					->check(function($post, $data){
						if (empty(trim((string)($data['url'] ?? ''))))
						{
							return $this->lang('Veuillez saisir une URL');
						}
					})
		)
		->rule($this->form_info($picker_inline))
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
		->rule($this->form_checkbox('enabled')
					->size('hb-switch-field')
					->data([
						'1' => 'Lien active'
					])
					->value($enabled)
		);
