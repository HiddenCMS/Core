<?php
/**
 * https://neofr.ag
 * @author: Michael BILCOT <michael.bilcot@neofr.ag>
 */

$parent_items = isset($model['parent_items']) && is_array($model['parent_items']) ? $model['parent_items'] : ['' => 'Aucun (niveau racine)'];
$enabled = array_key_exists('enabled', $model) ? (bool)$model['enabled'] : TRUE;
$front_urls = isset($model['front_urls']) && is_array($model['front_urls']) ? $model['front_urls'] : [];
$front_options = '';

foreach ($front_urls as $path => $label)
{
	$front_options .= '<option value="'.utf8_htmlentities($path).'">'.utf8_htmlentities($label).'</option>';
}

$picker_button = '<button type="button" class="btn btn-outline-secondary btn-sm" data-toggle="modal" data-target="#menu-url-picker-modal"><i class="fas fa-link"></i> '.$this->lang('Choisir un lien').'</button>';

$picker_modal = '	<div class="modal fade" id="menu-url-picker-modal" tabindex="-1" role="dialog" aria-hidden="true">
					<div class="modal-dialog modal-dialog-centered" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title">'.$this->lang('Type d\'URL').'</h5>
								<button type="button" class="close" data-dismiss="modal" aria-label="'.$this->lang('Fermer').'">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body">
								<div class="mb-3">
									<label class="col-form-label">'.$this->lang('Element front').'</label>
									<select class="form-control" id="menu-front-url-select">
										<option value="">'.$this->lang('Choisir un element').'</option>
										'.$front_options.'
									</select>
								</div>
								<div class="d-flex justify-content-between">
									<button type="button" class="btn btn-primary" id="menu-front-url-apply">'.$this->lang('Utiliser cet element').'</button>
									<button type="button" class="btn btn-outline-secondary" data-dismiss="modal" id="menu-custom-url-mode">'.$this->lang('Lien custom').'</button>
								</div>
							</div>
						</div>
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
		->rule($this->form_info($picker_button.$picker_modal))
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
