<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Outlines\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Admin extends Controller_Module
{
	public function index($outlines)
	{
		$this	->table()
				->add_columns([
					[
						'title'   => $this->lang('Outline'),
						'content' => function($outline){
							return $outline['title'].'<small class="ml-2"><code>'.$outline['name'].'</code></small>';
						},
						'sort'    => function($outline){
							return $outline['title'];
						}
					],
					[
						'title'   => $this->lang('Theme'),
						'content' => function($outline){
							return '<code>'.$outline['theme'].'</code>';
						},
						'sort'    => function($outline){
							return $outline['theme'];
						}
					],
					[
						'title'   => $this->lang('Base'),
						'content' => function($outline){
							return $outline['base'] ? icon('fas fa-check') : '';
						},
						'size'    => TRUE
					],
					[
						'content' => [
							function($outline){
								return '<a href="'.url('admin/live-editor?outline_id='.$outline['outline_id']).'" class="btn btn-sm btn-info">'.icon('fas fa-desktop').'</a>';
							},
							function($outline){
								return $this->button()
											->tooltip($this->lang('Dupliquer'))
											->icon('far fa-copy')
											->color('secondary')
											->compact()
											->outline()
											->modal_ajax('admin/outlines/'.$outline['outline_id'].'/duplicate');
							},
							function($outline){
								return $this->button_update('admin/outlines/'.$outline['outline_id'].'/'.url_title($outline['title']));
							},
							function($outline){
								return !$outline['base'] ? $this->button()
															->tooltip($this->lang('Supprimer'))
															->icon('far fa-trash-alt')
															->color('danger')
															->compact()
															->outline()
															->modal_ajax('admin/outlines/'.$outline['outline_id'].'/delete') : '';
							}
						],
						'size'    => TRUE
					]
				])
				->data($outlines)
				->no_data($this->lang('Il n\'y a pas encore d\'outline'));

		return $this->panel()
					->heading($this->lang('Outlines'), 'fas fa-layer-group')
					->body($this->table()->display())
					->footer($this->button_create('admin/outlines/add', $this->lang('Ajouter un outline')));
	}

	public function add()
	{
		$theme = $this->config->default_theme;

		$this	->subtitle($this->lang('Ajouter un outline'))
				->form()
				->add_rules('outlines', [
					'theme'   => $theme,
					'themes'  => $this->model()->get_themes(),
					'enabled' => TRUE
				])
				->add_submit($this->lang('Ajouter'))
				->add_back('admin/outlines');

		if ($this->form()->is_valid($post))
		{
			if (!($outline_id = $this->model()->add_outline(	$post['name'],
																$post['title'],
																$post['theme'],
																in_array('on', isset($post['base']) ? $post['base'] : []),
																in_array('on', isset($post['enabled']) ? $post['enabled'] : []))))
			{
				notify($this->lang('Impossible d\'enregistrer l\'outline'), 'danger');

				refresh();
			}

			notify($this->lang('Outline ajoute avec succes'));

			redirect('admin/live-editor?outline_id='.$outline_id);
		}
		else if (strtolower($_SERVER['REQUEST_METHOD']) == 'post' && ($errors = $this->form()->get_errors()))
		{
			notify(implode('<br />', array_map('strval', $errors)), 'danger');
		}

		return $this->panel()
					->heading($this->lang('Ajouter un outline'), 'fas fa-layer-group')
					->body($this->form()->display());
	}

	public function _edit($outline_id, $name, $title, $theme, $base, $enabled)
	{
		$this	->subtitle($title)
				->form()
				->add_rules('outlines', [
					'name'    => $name,
					'title'   => $title,
					'theme'   => $theme,
					'themes'  => $this->model()->get_themes(),
					'base'    => $base,
					'enabled' => $enabled
				])
				->add_submit($this->lang('Editer'))
				->add_back('admin/outlines');

		if ($this->form()->is_valid($post))
		{
			if (!$this->model()->edit_outline(	$outline_id,
												$post['name'],
												$post['title'],
												$post['theme'],
												in_array('on', isset($post['base']) ? $post['base'] : []),
												in_array('on', isset($post['enabled']) ? $post['enabled'] : [])))
			{
				notify($this->lang('Impossible d\'enregistrer l\'outline'), 'danger');

				refresh();
			}

			notify($this->lang('Outline edite avec succes'));

			redirect_back('admin/outlines');
		}
		else if (strtolower($_SERVER['REQUEST_METHOD']) == 'post' && ($errors = $this->form()->get_errors()))
		{
			notify(implode('<br />', array_map('strval', $errors)), 'danger');
		}

		return $this->panel()
					->heading($this->lang('Edition de l\'outline'), 'fas fa-layer-group')
					->body($this->form()->display())
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

										if ($name && !HiddenCMS()->db->from('outlines')->where('name', $name)->empty())
										{
											return $this->lang('Un outline utilise deja ce nom');
										}
									})
					)
					->success(function($data) use ($outline){
						if (!($outline_id = $this->model()->duplicate_outline($outline['outline_id'], $data['title'])))
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
						if (!$this->model()->delete_outline($outline['outline_id']))
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
