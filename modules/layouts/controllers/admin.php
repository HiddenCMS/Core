<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Layouts\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Admin extends Controller_Module
{
	public function index($outlines)
	{
		$this->css('layouts');

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
						'title'   => $this->lang('ThÃ¨me'),
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

		$this	->css('layouts')
				->subtitle($this->lang('Ajouter un outline'))
				->form()
				->add_rules('outlines', [
					'theme'   => $theme,
					'themes'  => $this->model()->get_themes(),
					'regions' => $this->model()->get_regions($theme),
					'widgets' => $this->model()->get_widgets(),
					'modules' => $this->model()->get_modules(),
					'layout'  => $this->storage->encode($this->model()->default_layout($theme)),
					'enabled' => TRUE
				])
				->add_submit($this->lang('Ajouter'))
				->add_back('admin/layouts');

		if ($this->form()->is_valid($post))
		{
			$layout = $this->storage->decode($post['layout'], []);

			if (!$this->model()->add_outline(	$post['name'],
												$post['title'],
												$post['theme'],
												$layout,
												in_array('on', isset($post['base']) ? $post['base'] : []),
												in_array('on', isset($post['enabled']) ? $post['enabled'] : [])))
			{
				notify($this->lang('Impossible d\'enregistrer l\'outline'), 'danger');

				refresh();
			}

			notify($this->lang('Outline ajoutÃ© avec succÃ¨s'));

			redirect_back('admin/layouts');
		}
		else if (strtolower($_SERVER['REQUEST_METHOD']) == 'post' && ($errors = $this->form()->get_errors()))
		{
			notify(implode('<br />', array_map('strval', $errors)), 'danger');
		}

		return $this->panel()
					->heading($this->lang('Ajouter un outline'), 'fas fa-layer-group')
					->body($this->form()->display());
	}

	public function _edit($outline_id, $name, $title, $theme, $layout, $base, $enabled)
	{
		$this	->css('layouts')
				->subtitle($title)
				->form()
				->add_rules('outlines', [
					'name'    => $name,
					'title'   => $title,
					'theme'   => $theme,
					'themes'  => $this->model()->get_themes(),
					'regions' => $this->model()->get_regions($theme),
					'widgets' => $this->model()->get_widgets(),
					'modules' => $this->model()->get_modules(),
					'layout'  => $layout,
					'base'    => $base,
					'enabled' => $enabled
				])
				->add_submit($this->lang('Ã‰diter'))
				->add_back('admin/layouts');

		if ($this->form()->is_valid($post))
		{
			$layout = $this->storage->decode($post['layout'], []);

			if (!$this->model()->edit_outline(	$outline_id,
												$post['name'],
												$post['title'],
												$post['theme'],
												$layout,
												in_array('on', isset($post['base']) ? $post['base'] : []),
												in_array('on', isset($post['enabled']) ? $post['enabled'] : [])))
			{
				notify($this->lang('Impossible d\'enregistrer l\'outline'), 'danger');

				refresh();
			}

			notify($this->lang('Outline Ã©ditÃ© avec succÃ¨s'));

			redirect_back('admin/layouts');
		}
		else if (strtolower($_SERVER['REQUEST_METHOD']) == 'post' && ($errors = $this->form()->get_errors()))
		{
			notify(implode('<br />', array_map('strval', $errors)), 'danger');
		}

		return $this->panel()
					->heading($this->lang('Ã‰dition de l\'outline'), 'fas fa-layer-group')
					->body($this->form()->display());
	}
}
