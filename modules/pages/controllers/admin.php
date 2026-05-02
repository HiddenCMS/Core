<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Pages\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Admin extends Controller_Module
{
	public function index($pages)
	{
		$this	->table()
				->add_columns([
					[
						'content' => function($data){
							return $data['published'] ? '<i class="fas fa-circle" data-toggle="tooltip" title="'.$this->lang('Publiée').'" style="color: #7bbb17;"></i>' : '<i class="far fa-circle" data-toggle="tooltip" title="'.$this->lang('En attente de publication').'" style="color: #535353;"></i>';
						},
						'sort'    => function($data){
							return $data['published'];
						},
						'size'    => TRUE
					],
					[
						'title'   => $this->lang('Titre de la page'),
						'content' => function($data){
							return $data['published'] ? '<a href="'.url($data['name']).'">'.$data['title'].'</a><small class="ml-2">'.$data['subtitle'].'</small>' : $data['title'];
						},
						'sort'    => function($data){
							return $data['title'];
						},
						'search'  => function($data){
							return $data['title'];
						}
					],
					[
						'title'   => $this->lang('Chemin d\'accès'),
						'content' => function($data){
							return '<code>/'.$data['name'].'</code>';
						},
						'sort'    => function($data){
							return $data['name'];
						},
						'search'  => function($data){
							return $data['name'];
						}
					],
					[
						'content' => [
							function($data){
								return $data['published'] ? $this->button()->tooltip($this->lang('Voir la page'))->icon('far fa-eye')->url($data['name'])->compact()->outline() : '';
							},
							function($data){
								return $this->user->admin ? $this->button_access($data['page_id'], 'page') : NULL;
							},
							function($data){
								return $this->is_authorized('modify_pages') ? $this->button_update('admin/pages/'.$data['page_id'].'/'.url_title($data['title'])) : NULL;
							},
							function($data){
								return $this->is_authorized('delete_pages') ? $this->button_delete('admin/pages/delete/'.$data['page_id'].'/'.url_title($data['title'])) : NULL;
							}
						],
						'size'    => TRUE
					]
				])
				->data($pages)
				->no_data($this->lang('Il n\'y a pas encore de page'));

		return $this->panel()
					->heading($this->lang('Liste des pages'), 'fas fa-bars')
					->body($this->table()->display())
					->footer_if($this->is_authorized('add_pages'), $this->button_create('admin/pages/add', $this->lang('Créer une page')));
	}

	public function add()
	{
		$this	->subtitle($this->lang('Ajouter une page'))
				->form()
				->add_rules('pages', [
					'modules'         => $this->model()->get_page_modules(),
					'news_categories' => $this->model()->get_news_categories()
				])
				->add_submit($this->lang('Ajouter'))
				->add_back('admin/pages');

		if ($this->form()->is_valid($post))
		{
			$instance = $this->model()->build_instance($post);

			$this->model()->add_page(	$post['name'],
										$post['title'],
										in_array('on', $post['published']),
										$post['subtitle'],
										$post['content'],
										$instance['module'],
										$instance['route'],
										$instance['settings']);

			notify($this->lang('Page ajoutée avec succès'));

			redirect_back('admin/pages');
		}

		return $this->panel()
					->heading($this->lang('Ajouter une page'), 'fas fa-align-left')
					->body($this->form()->display());
	}

	public function _edit($page_id, $name, $published, $title, $subtitle, $content, $tab)
	{
		$instance = $this->model()->get_instance($page_id) ?: [];
		$instance_values = $this->model()->get_instance_form_values($instance);

		$this	->subtitle($title)
				->form()
				->add_rules('pages', [
					'title'          => $title,
					'subtitle'       => $subtitle,
					'name'           => $name,
					'content'        => $content,
					'published'      => $published,
					'modules'        => $this->model()->get_page_modules(),
					'news_categories' => $this->model()->get_news_categories(),
					'module'         => $instance_values['module'],
					'news_category'  => $instance_values['news_category']
				])
				->add_submit($this->lang('Éditer'))
				->add_back('admin/pages');

		if ($this->form()->is_valid($post))
		{
			$instance = $this->model()->build_instance($post);

			$this->model()->edit_page(	$page_id,
										$post['name'],
										$post['title'],
										in_array('on', $post['published']),
										$post['subtitle'],
										$post['content'],
										$this->config->lang->info()->name,
										$instance['module'],
										$instance['route'],
										$instance['settings']);

			notify($this->lang('Page éditée avec succès'));

			redirect_back('admin/pages');
		}

		return $this->panel()
					->heading($this->lang('Édition de la page'), 'fas fa-align-left')
					->body($this->form()->display());
	}

	public function delete($page_id, $title)
	{
		$this	->title($this->lang('Suppression d\'une page'))
				->subtitle($title)
				->form()
				->confirm_deletion($this->lang('Confirmation de suppression'), $this->lang('Êtes-vous sûr(e) de vouloir supprimer la page <b>%s</b> ?', $title));

		if ($this->form()->is_valid())
		{
			$this->model()->delete_page($page_id);

			return 'OK';
		}

		return $this->form()->display();
	}
}


