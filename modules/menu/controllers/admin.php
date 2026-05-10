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

		$table = $this	->table2($this->array($menus), $this->lang('Il n\'y a pas encore de menu'))
						->col($this->lang('Titre'), function($data){
							return '<a href="'.url('admin/menu/items/'.$data['menu_id'].'/'.$data['name']).'">'.$data['title'].'</a>';
						})
						->col($this->lang('Chemin d\'acces'), function($data){
							return '<code>'.$data['name'].'</code>';
						})
						->col($this->lang('Liens'), 'compact', 'center', function($data){
							return '<span class="badge badge-secondary">'.$data['nb_items'].'</span>';
						})
						->compact(function($data){
							$actions = [
								$this->button()->icon('fas fa-list')->tooltip($this->lang('Gerer les liens'))->url('admin/menu/items/'.$data['menu_id'].'/'.$data['name'])->compact()->outline()
							];

							if ($this->is_authorized('modify_menus'))
							{
								$actions[] = $this->button_update('admin/menu/'.$data['menu_id'].'/'.$data['name']);
							}

							if ($this->is_authorized('delete_menus'))
							{
								$actions[] = $this->button_delete('admin/menu/delete/'.$data['menu_id'].'/'.$data['name']);
							}

							return implode('&nbsp;', array_filter($actions));
						});

		return $this	->panel()
					->heading($this->lang('Liste des menus'), 'fas fa-bars')
					->body($table->panel()->body(), FALSE)
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
		$this->css('menu_nested');

		$children = [];

		foreach ($items as $item)
		{
			$parent_id = !empty($item['parent_id']) ? (int)$item['parent_id'] : 0;

			if (!isset($children[$parent_id]))
			{
				$children[$parent_id] = [];
			}

			$children[$parent_id][] = $item;
		}

		$render = function($parent_id = 0, $level = 0) use (&$render, $children, $menu){
			if (empty($children[$parent_id]))
			{
				return '';
			}

			$html = '<ul class="menu-nested-sortable '.(!$level ? 'menu-nested-root' : 'menu-nested-children').'">';

			foreach ($children[$parent_id] as $item)
			{
				$actions = [];

				if ($this->is_authorized('modify_menus'))
				{
					$actions[] = $this->button_update('admin/menu/items/'.$menu['menu_id'].'/'.$menu['name'].'/edit/'.$item['item_id']);
				}

				if ($this->is_authorized('delete_menus'))
				{
					$actions[] = $this->button_delete('admin/menu/items/'.$menu['menu_id'].'/'.$menu['name'].'/delete/'.$item['item_id'].'/'.url_title($item['title']));
				}

				$sort = '';

				if ($this->is_authorized('modify_menus'))
				{
					$sort = (string)$this->button_sort($item['item_id'], 'admin/ajax/menu/items/sort', '.menu-nested-sortable', 'li.menu-nested-item')
										->data('level', (int)$item['level'])
										->data('parent-id', (int)$item['parent_id'])
										->data('tree', 1);
				}

				$target = $item['target'] == '_blank' ? $this->lang('Nouvelle fenetre') : $this->lang('Meme fenetre');
				$status = $item['enabled'] ? '<i class="fas fa-circle text-success" data-toggle="tooltip" title="'.$this->lang('Active').'"></i>' : '<i class="far fa-circle text-muted" data-toggle="tooltip" title="'.$this->lang('Desactive').'"></i>';

				$html .= '<li class="menu-nested-item" data-item-id="'.$item['item_id'].'">'
						.'<div class="menu-nested-row">'
							.'<div class="menu-nested-main">'
								.$sort
								.'<span class="menu-nested-title">'.$item['title'].'</span>'
								.'<code class="menu-nested-url">'.$item['url'].'</code>'
								.'<span class="badge badge-light">'.$target.'</span>'
							.'</div>'
							.'<div class="menu-nested-side">'
								.$status
								.' '.implode('&nbsp;', array_filter($actions))
							.'</div>'
						.'</div>'
						.$render((int)$item['item_id'], $level + 1)
					.'</li>';
			}

			$html .= '</ul>';

			return $html;
		};

		$body = !empty($items) ? $render() : '<div class="alert alert-info mb-0">'.$this->lang('Il n\'y a pas encore de lien').'</div>';

		return $this	->panel()
					->heading($this->lang('Liens du menu : %s', $menu['title']), 'fas fa-list')
					->body($body, FALSE)
					->footer(($this->is_authorized('modify_menus') ? $this->button_create('admin/menu/items/'.$menu['menu_id'].'/'.$menu['name'].'/add', $this->lang('Ajouter un lien')) : '').' '.$this->button()->label($this->lang('Retour'))->url('admin/menu'));
	}

	public function _items_add($menu)
	{
		$this->subtitle($this->lang('Ajouter un lien'));
		$this->js('menu_item_url_picker');

		$front_urls = $this->menu_model()->get_front_url_choices();

		return $this	->form2('menu_item', [
						'menu_id'      => $menu['menu_id'],
						'front_urls'   => $front_urls,
						'target'       => '_parent',
						'parent_items' => $this->menu_model()->get_parent_items($menu['menu_id']),
						'position'     => $this->menu_model()->next_position($menu['menu_id']),
						'enabled'      => TRUE
					])
					->success(function($data, $form) use ($menu){
						$url = trim((string)($data['url'] ?? ''));

						if ($url !== '/')
						{
							$url = trim($url, '/');
						}

						if ($url === '')
						{
							$form->error($this->lang('Veuillez saisir une URL'));
							return;
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
		$this->js('menu_item_url_picker');

		$front_urls = $this->menu_model()->get_front_url_choices();

		return $this	->form2('menu_item', [
						'item_id'      => $item['item_id'],
						'menu_id'      => $menu['menu_id'],
						'title'        => $item['title'],
						'front_urls'   => $front_urls,
						'url'          => $item['url'],
						'target'       => $item['target'],
						'parent_id'    => $item['parent_id'],
						'position'     => $item['position'],
						'enabled'      => (bool)$item['enabled'],
						'parent_items' => $this->menu_model()->get_parent_items($menu['menu_id'], $item['item_id'])
					])
					->success(function($data, $form) use ($item, $menu){
						$url = trim((string)($data['url'] ?? ''));

						if ($url !== '/')
						{
							$url = trim($url, '/');
						}

						if ($url === '')
						{
							$form->error($this->lang('Veuillez saisir une URL'));
							return;
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
