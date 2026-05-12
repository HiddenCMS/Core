<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Outlines\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Admin extends Controller_Module
{
	private function outline_model()
	{
		return $this->module->model2('outline');
	}

	private function checkbox_enabled($values)
	{
		return is_array($values) && in_array('on', $values, TRUE);
	}

	public function index($outlines)
	{
		$actions = function($outline){
			$buttons = [
				'<a href="'.url('admin/live-editor?outline_id='.$outline['outline_id']).'" class="btn btn-sm btn-info">'.icon('fas fa-desktop').'</a>',
				(string)$this->button_duplicate_modal($this->_duplicate($outline)),
				(string)$this->button_update('admin/outlines/'.$outline['outline_id'].'/'.url_title($outline['title']))
			];

			if (!$outline['base'])
			{
				$buttons[] = (string)$this->button()
										->tooltip($this->lang('Supprimer'))
										->icon('far fa-trash-alt')
										->color('danger')
										->compact()
										->outline()
										->modal($this->_delete($outline));
			}

			return '<span style="white-space: nowrap;">'.implode('&nbsp;', array_filter($buttons)).'</span>';
		};

		$table = $this	->table2($this->array($outlines), $this->lang('Il n\'y a pas encore d\'outline'))
						->col($this->lang('Outline'), function($outline){
							return $outline['title'].'<small class="ml-2"><code>'.$outline['name'].'</code></small>';
						})
						->col($this->lang('Theme'), function($outline){
							return '<code>'.$outline['theme'].'</code>';
						})
						->col($this->lang('Base'), 'compact', 'center', function($outline){
							return $outline['base'] ? icon('fas fa-check') : '';
						})
						->col($this	->table_col()
									->title($this->lang('Actions'))
									->align('right')
									->style('text-nowrap')
									->content($actions)
						);

		return $table->panel()
					->title($this->lang('Outlines'), 'fas fa-layer-group')
					->footer($this->button_create('admin/outlines/add', $this->lang('Ajouter un outline')));
	}

	public function add()
	{
		$this->subtitle($this->lang('Ajouter un outline'));

		return $this	->form2('outline', [
						'outline_id' => 0,
						'theme'      => $this->config->default_theme,
						'themes'     => $this->outline_model()->get_themes(),
						'enabled'    => TRUE
					])
					->success(function($data, $form){
						if (!($outline_id = $this->outline_model()->add_outline(
							trim((string)($data['name'] ?? '')),
							trim((string)($data['title'] ?? '')),
							trim((string)($data['theme'] ?? '')),
							$this->checkbox_enabled($data['base'] ?? []),
							$this->checkbox_enabled($data['enabled'] ?? [])
						)))
						{
							$form->error($this->lang('Impossible d\'enregistrer l\'outline'));
							return;
						}

						notify($this->lang('Outline ajoute avec succes'));
						redirect('admin/live-editor?outline_id='.$outline_id);
					})
					->submit($this->lang('Ajouter'))
					->back('admin/outlines')
					->panel()
					->heading($this->lang('Ajouter un outline'), 'fas fa-layer-group');
	}

	public function _edit($outline_id, $name, $title, $theme, $base, $enabled)
	{
		$this->subtitle($title);

		return $this	->form2('outline', [
						'outline_id' => (int)$outline_id,
						'name'       => $name,
						'title'      => $title,
						'theme'      => $theme,
						'themes'     => $this->outline_model()->get_themes(),
						'base'       => (bool)$base,
						'enabled'    => (bool)$enabled
					])
					->success(function($data, $form) use ($outline_id){
						$result = $this->outline_model()->edit_outline(
							(int)$outline_id,
							trim((string)($data['name'] ?? '')),
							trim((string)($data['title'] ?? '')),
							trim((string)($data['theme'] ?? '')),
							$this->checkbox_enabled($data['base'] ?? []),
							$this->checkbox_enabled($data['enabled'] ?? [])
						);

						if (!$result)
						{
							$form->error($this->lang('Impossible d\'enregistrer l\'outline'));
							return;
						}

						notify($this->lang('Outline edite avec succes'));
						redirect_back('admin/outlines');
					})
					->submit($this->lang('Editer'))
					->back('admin/outlines')
					->panel()
					->heading($this->lang('Edition de l\'outline'), 'fas fa-layer-group')
					->footer('<a href="'.url('admin/live-editor?outline_id='.$outline_id).'" class="btn btn-info">'.icon('fas fa-desktop').' '.$this->lang('Editer visuellement').'</a>');
	}

	public function _duplicate($outline)
	{
		return $this	->form2()
					->rule($this	->form_text('title')
									->title($this->lang('Nom du nouvel outline'))
									->value($this->lang('Copie de %s', $outline['title']))
									->required()
									->check(function($post){
										$name = !empty($post['title']) ? url_title($post['title']) : '';

										if ($name && $this->outline_model()->name_exists($name))
										{
											return $this->lang('Un outline utilise deja ce nom');
										}
									})
					)
					->success(function($data) use ($outline){
						if (!($outline_id = $this->outline_model()->duplicate_outline($outline['outline_id'], $data['title'])))
						{
							notify($this->lang('Impossible de dupliquer l\'outline'), 'danger');
							refresh();
						}

						notify($this->lang('Outline duplique avec succes'));

						redirect('admin/live-editor?outline_id='.$outline_id);
					})
					->submit($this->lang('Dupliquer'))
					->modal($this->lang('Dupliquer %s', $outline['title']), 'far fa-copy')
					->cancel();
	}

	public function _delete($outline)
	{
		return $this	->modal($this->lang('Supprimer %s', $outline['title']), 'far fa-trash-alt text-danger')
					->body($this->lang('Les pages utilisant cet outline seront rattachees a l\'outline de base.'))
					->callback(function() use ($outline){
						if (!$this->outline_model()->delete_outline($outline['outline_id']))
						{
							notify($this->lang('Impossible de supprimer l\'outline'), 'danger');
							refresh();
						}

						notify($this->lang('Outline supprime avec succes'));

						redirect('admin/outlines');
					})
					->submit($this->lang('Supprimer'), 'danger')
					->cancel();
	}
}
