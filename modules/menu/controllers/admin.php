<?php
/**
 * https://neofr.ag
 * @author: Michael BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Menu\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Admin extends Controller_Module
{
	private function menu_model()
	{
		return $this->module->model2('menu');
	}

	public function index($menus)
	{
		$this->title($this->lang('Menus'));

		$this	->table()
				->add_columns([
					[
						'title'   => $this->lang('Titre'),
						'content' => function($data){
							return '<a href="'.url('admin/menu/items/'.$data['menu_id'].'/'.$data['name']).'">'.$data['title'].'</a>';
						},
						'sort'    => function($data){
							return $data['title'];
						},
						'search'  => function($data){
							return $data['title'];
						}
					],
					[
						'title'   => $this->lang('Chemin d\'acces'),
						'content' => function($data){
							return '<code>'.$data['name'].'</code>';
						},
						'sort'    => function($data){
							return $data['name'];
						},
						'search'  => function($data){
							return $data['name'];
						}
					],
					[
						'title'   => $this->lang('Liens'),
						'content' => function($data){
							return '<span class="badge badge-secondary">'.$data['nb_items'].'</span>';
						},
						'sort'    => function($data){
							return $data['nb_items'];
						},
						'size'    => TRUE
					],
					[
						'content' => [
							function($data){
								return $this->button()->icon('fas fa-list')->tooltip($this->lang('Gerer les liens'))->url('admin/menu/items/'.$data['menu_id'].'/'.$data['name'])->compact()->outline();
							},
							function($data){
								return $this->is_authorized('modify_menus') ? $this->button_update('admin/menu/'.$data['menu_id'].'/'.$data['name']) : NULL;
							},
							function($data){
								return $this->is_authorized('delete_menus') ? $this->button_delete('admin/menu/delete/'.$data['menu_id'].'/'.$data['name']) : NULL;
							}
						],
						'size'    => TRUE
					]
				])
				->data($menus)
				->no_data($this->lang('Il n\'y a pas encore de menu'));

		return $this->panel()
					->heading($this->lang('Liste des menus'), 'fas fa-bars')
					->body($this->table()->display())
					->footer_if($this->is_authorized('add_menus'), $this->button_create('admin/menu/add', $this->lang('Creer un menu')));
	}

	public function add()
	{
		$this->subtitle($this->lang('Ajouter un menu'));

		return $this	->form2('menu', [
						'menu_id' => 0
					])
					->success(function($data, $form){
						$title = trim($data['title']);
						$name = url_title(!empty($data['name']) ? $data['name'] : $title);

						if ($this->menu_model()->name_exists($name))
						{
							$form->error($this->lang('Chemin d\'acces deja utilise'));
							return;
						}

						$this->menu_model()->add_menu($name, $title);

						notify($this->lang('Menu ajoute avec succes'));

						redirect_back('admin/menu');
					})
					->submit($this->lang('Ajouter'))
					->back('admin/menu')
					->panel()
					->heading($this->lang('Ajouter un menu'), 'fas fa-bars');
	}

	public function _edit($menu_id, $name, $title)
	{
		$this->subtitle($title);

		return $this	->form2('menu', [
						'menu_id' => $menu_id,
						'name'    => $name,
						'title'   => $title
					])
					->success(function($data, $form) use ($menu_id){
						$title = trim($data['title']);
						$name = url_title(!empty($data['name']) ? $data['name'] : $title);

						if ($this->menu_model()->name_exists($name, $menu_id))
						{
							$form->error($this->lang('Chemin d\'acces deja utilise'));
							return;
						}

						$this->menu_model()->edit_menu($menu_id, $name, $title);

						notify($this->lang('Menu edite avec succes'));

						redirect_back('admin/menu');
					})
					->submit($this->lang('Editer'))
					->back('admin/menu')
					->panel()
					->heading($this->lang('Editer le menu'), 'fas fa-bars');
	}

	public function delete($menu_id, $title)
	{
		return $this	->modal($this->lang('Suppression d\'un menu'), 'far fa-trash-alt text-danger')
					->body($this->lang('Etes-vous sur(e) de vouloir supprimer le menu <b>%s</b> ?', $title))
					->callback(function() use ($menu_id){
						$this->menu_model()->delete_menu($menu_id);
						notify($this->lang('Menu supprime avec succes'));
						refresh();
					})
					->submit($this->lang('Supprimer'), 'danger')
					->cancel();
	}

	public function _items($menu, $items)
	{
		$this	->table()
				->add_columns([
					[
						'content' => function($data){
							return $this->is_authorized('modify_menus') ? $this->button_sort($data['item_id'], 'admin/ajax/menu/items/sort') : NULL;
						},
						'size'    => TRUE
					],
					[
						'title'   => $this->lang('Titre'),
						'content' => function($data){
							return $data['level'] ? str_repeat('&mdash; ', (int)$data['level']).$data['title'] : $data['title'];
						},
						'search'  => function($data){
							return $data['title'];
						},
						'sort'    => function($data){
							return $data['title'];
						}
					],
					[
						'title'   => $this->lang('URL'),
						'content' => function($data){
							return '<code>'.$data['url'].'</code>';
						},
						'search'  => function($data){
							return $data['url'];
						}
					],
					[
						'title'   => $this->lang('Cible'),
						'content' => function($data){
							return $data['target'] == '_blank' ? $this->lang('Nouvelle fenetre') : $this->lang('Meme fenetre');
						},
						'size'    => TRUE
					],
					[
						'title'   => $this->lang('Ordre'),
						'content' => function($data){
							return $data['position'];
						},
						'sort'    => function($data){
							return $data['position'];
						},
						'size'    => TRUE
					],
					[
						'content' => function($data){
							return $data['enabled'] ? '<i class="fas fa-circle" data-toggle="tooltip" title="'.$this->lang('Active').'" style="color: #7bbb17;"></i>' : '<i class="far fa-circle" data-toggle="tooltip" title="'.$this->lang('Desactive').'" style="color: #535353;"></i>';
						},
						'size'    => TRUE
					],
					[
						'content' => [
							function($data) use ($menu){
								return $this->is_authorized('modify_menus') ? $this->button_update('admin/menu/items/'.$menu['menu_id'].'/'.$menu['name'].'/edit/'.$data['item_id']) : NULL;
							},
							function($data) use ($menu){
								return $this->is_authorized('delete_menus') ? $this->button_delete('admin/menu/items/'.$menu['menu_id'].'/'.$menu['name'].'/delete/'.$data['item_id'].'/'.url_title($data['title'])) : NULL;
							}
						],
						'size'    => TRUE
					]
				])
				->pagination(FALSE)
				->data($items)
				->no_data($this->lang('Il n\'y a pas encore de lien'));

		return $this->panel()
					->heading($this->lang('Liens du menu : %s', $menu['title']), 'fas fa-list')
					->body($this->table()->display())
					->footer(($this->is_authorized('modify_menus') ? $this->button_create('admin/menu/items/'.$menu['menu_id'].'/'.$menu['name'].'/add', $this->lang('Ajouter un lien')) : '').' '.$this->button()->label($this->lang('Retour'))->url('admin/menu'));
	}

	public function _items_add($menu)
	{
		$this->subtitle($this->lang('Ajouter un lien'));

		$front_urls = $this->menu_model()->get_front_url_choices();

		return $this	->form2('menu_item', [
						'menu_id'      => $menu['menu_id'],
						'url_mode'     => 'custom',
						'front_urls'   => $front_urls,
						'target'       => '_parent',
						'parent_items' => $this->menu_model()->get_parent_items($menu['menu_id']),
						'position'     => $this->menu_model()->next_position($menu['menu_id']),
						'enabled'      => TRUE
					])
					->success(function($data, $form) use ($menu){
						$url = '';

						if (($data['url_mode'] ?? 'custom') === 'front')
						{
							$url = trim((string)($data['front_url'] ?? ''));
							if ($url !== '/')
							{
								$url = trim($url, '/');
							}

							if ($url === '')
							{
								$form->error($this->lang('Veuillez selectionner un element front'));
								return;
							}
						}
						else
						{
							$url = trim((string)($data['url'] ?? ''));

							if ($url === '')
							{
								$form->error($this->lang('Veuillez saisir une URL'));
								return;
							}
						}

						$parent_id = !empty($data['parent_id']) ? (int)$data['parent_id'] : NULL;

						if (!$this->menu_model()->is_parent_depth_allowed($menu['menu_id'], $parent_id))
						{
							$form->error($this->lang('Profondeur maximale atteinte (3 sous-niveaux maximum)'));
							return;
						}

						$this->menu_model()->add_item(
							$menu['menu_id'],
							trim($data['title']),
							$url,
							!empty($data['target']) ? $data['target'] : '_parent',
							$parent_id,
							isset($data['position']) && $data['position'] !== '' ? (int)$data['position'] : 0,
							!empty($data['enabled']) && in_array('1', $data['enabled'], TRUE)
						);

						notify($this->lang('Lien ajoute avec succes'));

						redirect_back('admin/menu/items/'.$menu['menu_id'].'/'.$menu['name']);
					})
					->submit($this->lang('Ajouter'))
					->back('admin/menu/items/'.$menu['menu_id'].'/'.$menu['name'])
					->panel()
					->heading($this->lang('Ajouter un lien : %s', $menu['title']), 'fas fa-list');
	}

	public function _items_edit($menu, $item)
	{
		$this->subtitle($this->lang('Editer un lien'));

		$front_urls = $this->menu_model()->get_front_url_choices();
		$url_mode = array_key_exists($item['url'], $front_urls) ? 'front' : 'custom';

		return $this	->form2('menu_item', [
						'item_id'      => $item['item_id'],
						'menu_id'      => $menu['menu_id'],
						'title'        => $item['title'],
						'url_mode'     => $url_mode,
						'front_urls'   => $front_urls,
						'front_url'    => $url_mode === 'front' ? $item['url'] : '',
						'url'          => $url_mode === 'custom' ? $item['url'] : '',
						'target'       => $item['target'],
						'parent_id'    => $item['parent_id'],
						'position'     => $item['position'],
						'enabled'      => (bool)$item['enabled'],
						'parent_items' => $this->menu_model()->get_parent_items($menu['menu_id'], $item['item_id'])
					])
					->success(function($data, $form) use ($item, $menu){
						$url = '';

						if (($data['url_mode'] ?? 'custom') === 'front')
						{
							$url = trim((string)($data['front_url'] ?? ''));
							if ($url !== '/')
							{
								$url = trim($url, '/');
							}

							if ($url === '')
							{
								$form->error($this->lang('Veuillez selectionner un element front'));
								return;
							}
						}
						else
						{
							$url = trim((string)($data['url'] ?? ''));

							if ($url === '')
							{
								$form->error($this->lang('Veuillez saisir une URL'));
								return;
							}
						}

						$parent_id = !empty($data['parent_id']) ? (int)$data['parent_id'] : NULL;

						if (!$this->menu_model()->is_parent_depth_allowed($menu['menu_id'], $parent_id))
						{
							$form->error($this->lang('Profondeur maximale atteinte (3 sous-niveaux maximum)'));
							return;
						}

						$this->menu_model()->edit_item(
							$item['item_id'],
							trim($data['title']),
							$url,
							!empty($data['target']) ? $data['target'] : '_parent',
							$parent_id,
							isset($data['position']) && $data['position'] !== '' ? (int)$data['position'] : 0,
							!empty($data['enabled']) && in_array('1', $data['enabled'], TRUE)
						);

						notify($this->lang('Lien edite avec succes'));

						redirect_back('admin/menu/items/'.$menu['menu_id'].'/'.$menu['name']);
					})
					->submit($this->lang('Editer'))
					->back('admin/menu/items/'.$menu['menu_id'].'/'.$menu['name'])
					->panel()
					->heading($this->lang('Editer un lien : %s', $menu['title']), 'fas fa-list');
	}

	public function _items_delete($menu, $item_id, $title)
	{
		return $this	->modal($this->lang('Suppression d\'un lien'), 'far fa-trash-alt text-danger')
					->body($this->lang('Etes-vous sur(e) de vouloir supprimer le lien <b>%s</b> ?', $title))
					->callback(function() use ($menu, $item_id){
						$this->menu_model()->delete_item($item_id);
						notify($this->lang('Lien supprime avec succes'));
						redirect('admin/menu/items/'.$menu['menu_id'].'/'.$menu['name']);
					})
					->submit($this->lang('Supprimer'), 'danger')
					->cancel();
	}
}
