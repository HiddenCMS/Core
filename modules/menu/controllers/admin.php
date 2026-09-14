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

	private function menu_index_url($menu_id = 0)
	{
		$url = 'admin/menu';

		if ((int)$menu_id > 0)
		{
			$url .= '?menu_id='.(int)$menu_id;
		}

		return $url;
	}

	private function component($name, array $data, $fallback = '')
	{
		$template = HB()->template('menu/'.$name, $data, $fallback);

		if ($theme = $this->output->theme())
		{
			$template->owner($theme);
		}

		return (string)$template->owner($this->module);
	}

	public function index($menus)
	{
		$this->title($this->lang('Menus'));
		$this->css('menu_nested');
		$menus = array_values((array)$menus);
		$selected_menu_id = (int)$this->input->get->get('menu_id');
		$selected_menu = NULL;

		foreach ($menus as $menu)
		{
			if ((int)$menu['menu_id'] === $selected_menu_id)
			{
				$selected_menu = $menu;
				break;
			}
		}

		if (!$selected_menu && !empty($menus))
		{
			$selected_menu = $menus[0];
			$selected_menu_id = (int)$selected_menu['menu_id'];
		}

		$left_body = '';

		if (empty($menus))
		{
			$left_body = '<div class="table-empty">'.$this->lang('There are no menus yet').'</div>';
		}
		else
		{
			$left_body = '<div class="hb-menu-master-list">';

			foreach ($menus as $menu)
			{
				$is_active = ((int)$menu['menu_id'] === $selected_menu_id);
				$select_url = url('admin/menu?menu_id='.(int)$menu['menu_id']);
				$item_actions = [];

				if ($this->is_authorized('modify_menus'))
				{
					$item_actions[] = (string)$this->button_update('', $this->lang('Edit'))
														->modal_ajax('admin/ajax/menu/'.$menu['menu_id'].'/'.$menu['name']);
				}

				if ($this->is_authorized('delete_menus'))
				{
					$item_actions[] = $this->button_delete('admin/ajax/menu/delete/'.$menu['menu_id'].'/'.$menu['name']);
				}

				$left_body .= '<div class="hb-menu-master-item'.($is_active ? ' is-active' : '').'">'
							.'<a class="hb-menu-master-main" href="'.$select_url.'">'
								.'<span class="hb-menu-master-title">'.$menu['title'].'</span>'
								.'<span class="hb-menu-master-meta">'.$this->component('technical_name', [
									'value' => $menu['name'],
									'class' => ''
								], '<code>'.utf8_htmlentities($menu['name']).'</code>').' <strong>'.(int)$menu['nb_items'].'</strong></span>'
							.'</a>'
							.'<div class="hb-menu-master-actions">'.implode('', array_filter($item_actions)).'</div>'
						.'</div>';
			}

			$left_body .= '</div>';
		}

		$left_panel = $this	->panel()
							->style('hb-menu-master-panel')
							->heading($this->lang('Menu list'), 'fas fa-bars')
							->heading_if(
								$this->is_authorized('add_menus'),
								$this->button_create()
									->title('')
									->tooltip($this->lang('Create a menu'))
									->modal_ajax('admin/ajax/menu/add')
									->compact()
									->align('right')
							)
							->body($left_body, FALSE);

		$right_body = '<div class="table-empty">'.$this->lang('Select a menu').'</div>';
		$right_title = $this->lang('Menu preview');
		$right_header_action = NULL;

		if ($selected_menu)
		{
			$selected_items = $this->menu_model()->get_menu_items($selected_menu['menu_id']);

			$right_title = $this->lang('Menu links: %s', $selected_menu['title']);
			if ($this->is_authorized('modify_menus'))
			{
				$right_header_action = $this->button_create()
											->title('')
											->tooltip($this->lang('Add a link'))
											->modal_ajax('admin/ajax/menu/items/'.$selected_menu['menu_id'].'/'.$selected_menu['name'].'/add')
											->compact()
											->align('right');
			}
			$right_body = $this->render_items_tree($selected_menu, $selected_items);
		}

		$right_panel = $this	->panel()
							->heading($right_title, 'fas fa-stream')
							->heading_if(
								(bool)$right_header_action,
								$right_header_action
							)
							->body($right_body, FALSE);

		return '<div class="ui stackable grid menu-admin-layout">'
				.'<div class="sixteen wide mobile four wide computer column">'.$left_panel.'</div>'
				.'<div class="sixteen wide mobile twelve wide computer column">'.$right_panel.'</div>'
			.'</div>';
	}

	public function add()
	{
		return $this	->form2('menu', [
						'menu_id' => 0
					])
					->success(function($data, $form){
						$title = trim($data['title']);
						$name = url_title(!empty($data['name']) ? $data['name'] : $title);

						if ($this->menu_model()->name_exists($name))
						{
							$form->error($this->lang('Path already in use'));
							return;
						}

						$this->menu_model()->add_menu($name, $title);

						notify($this->lang('Menu added successfully'));

						redirect($this->menu_index_url());
					})
					->submit($this->lang('Add'))
					->modal($this->lang('Add a menu'), 'fas fa-bars')
					->cancel();
	}

	public function _edit($menu_id, $name, $title)
	{
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
							$form->error($this->lang('Path already in use'));
							return;
						}

						$this->menu_model()->edit_menu($menu_id, $name, $title);

						notify($this->lang('Menu updated successfully'));

						redirect($this->menu_index_url($menu_id));
					})
					->submit($this->lang('Edit'))
					->modal($this->lang('Edit menu'), 'fas fa-bars')
					->cancel();
	}

	public function delete($menu_id, $title)
	{
		return $this	->modal($this->lang('Delete a menu'), 'far fa-trash-alt text-danger')
					->body($this->lang('Are you sure you want to delete the <b>%s</b> menu?', $title))
					->callback(function() use ($menu_id){
						$this->menu_model()->delete_menu($menu_id);
						notify($this->lang('Menu deleted successfully'));
						refresh();
					})
					->submit($this->lang('Delete'), 'danger')
					->cancel();
	}

	public function _items($menu, $items)
	{
		redirect($this->menu_index_url($menu['menu_id']));
	}

	private function render_items_tree($menu, $items)
	{
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

				if ($this->user->admin)
				{
					$actions[] = $this->button_access($item['item_id'], 'link', 'menu', $this->lang('Visibility permissions'));
				}

				if ($this->is_authorized('modify_menus'))
				{
					$actions[] = (string)$this->button_update('', $this->lang('Edit'))
													->modal_ajax('admin/ajax/menu/items/'.$menu['menu_id'].'/'.$menu['name'].'/edit/'.$item['item_id']);
				}

				if ($this->is_authorized('delete_menus'))
				{
					$actions[] = $this->button_delete('admin/ajax/menu/items/'.$menu['menu_id'].'/'.$menu['name'].'/delete/'.$item['item_id'].'/'.url_title($item['title']));
				}

				$sort = '';

				if ($this->is_authorized('modify_menus'))
				{
					$sort = (string)$this->button_sort($item['item_id'], 'admin/ajax/menu/items/sort', '.menu-nested-sortable', '> li.menu-nested-item')
										->data('level', (int)$item['level'])
										->data('parent-id', (int)$item['parent_id'])
										->data('tree', 1);
				}

				$status_title = $item['enabled'] ? $this->lang('Active') : $this->lang('Disabled');
				$status = '<i class="menu-nested-status-icon '.($item['enabled'] ? 'fas fa-circle is-active' : 'far fa-circle is-disabled').'" data-toggle="tooltip" title="'.$status_title.'" aria-label="'.$status_title.'"></i>';

				$html .= '<li class="menu-nested-item" data-item-id="'.$item['item_id'].'">'
						.'<div class="menu-nested-row">'
							.'<div class="menu-nested-main">'
								.$sort
								.'<span class="menu-nested-title">'.$item['title'].'</span>'
								.$this->component('technical_name', [
									'value' => $item['url'],
									'class' => 'menu-nested-url'
								], '<code class="menu-nested-url">'.utf8_htmlentities($item['url']).'</code>')
							.'</div>'
							.'<div class="menu-nested-side">'
								.$status
								.implode('', array_filter($actions))
							.'</div>'
						.'</div>'
						.$render((int)$item['item_id'], $level + 1)
					.'</li>';
			}

			$html .= '</ul>';

			return $html;
		};

		return !empty($items) ? $render() : '<div class="table-empty">'.$this->lang('There are no links yet').'</div>';
	}

	public function _items_add($menu)
	{
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
							$form->error($this->lang('Please enter a URL'));
							return;
						}

						$parent_id = !empty($data['parent_id']) ? (int)$data['parent_id'] : NULL;

						if (!$this->menu_model()->is_parent_depth_allowed($menu['menu_id'], $parent_id))
						{
							$form->error($this->lang('Maximum depth reached (up to 3 nested levels)'));
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

						notify($this->lang('Link added successfully'));

						redirect($this->menu_index_url($menu['menu_id']));
					})
					->submit($this->lang('Add'))
					->modal($this->lang('Add a link: %s', $menu['title']), 'fas fa-list')
					->cancel();
	}

	public function _items_edit($menu, $item)
	{
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
							$form->error($this->lang('Please enter a URL'));
							return;
						}

						$parent_id = !empty($data['parent_id']) ? (int)$data['parent_id'] : NULL;

						if (!$this->menu_model()->is_parent_depth_allowed($menu['menu_id'], $parent_id))
						{
							$form->error($this->lang('Maximum depth reached (up to 3 nested levels)'));
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

						notify($this->lang('Link updated successfully'));

						redirect($this->menu_index_url($menu['menu_id']));
					})
					->submit($this->lang('Edit'))
					->modal($this->lang('Edit a link: %s', $menu['title']), 'fas fa-list')
					->cancel();
	}

	public function _items_delete($menu, $item_id, $title)
	{
		return $this	->modal($this->lang('Delete a link'), 'far fa-trash-alt text-danger')
					->body($this->lang('Are you sure you want to delete the <b>%s</b> link?', $title))
					->callback(function() use ($menu, $item_id){
						$this->menu_model()->delete_item($item_id);
						notify($this->lang('Link deleted successfully'));
						redirect($this->menu_index_url($menu['menu_id']));
					})
					->submit($this->lang('Delete'), 'danger')
					->cancel();
	}
}
