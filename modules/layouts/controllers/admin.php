<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Layouts\Controllers;

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
								return $this->button_update('admin/layouts/'.$outline['outline_id'].'/'.url_title($outline['title']));
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
					->footer($this->button_create('admin/layouts/add', $this->lang('Ajouter un outline')));
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
				->add_back('admin/layouts');

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
				->add_back('admin/layouts');

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

			redirect_back('admin/layouts');
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
}
