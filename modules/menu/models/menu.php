<?php
/**
 * https://neofr.ag
 * @author: Michael BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Menu\Models;

use HB\HiddenCMS\Loadables\Model2;

class Menu extends Model2
{
	public $__table = 'menus';
	const MAX_SUBLEVELS = 3;

	static public function __schema()
	{
		return [
			'menu_id' => self::field()->primary(),
			'name'    => self::field()->text(100),
			'title'   => self::field()->text(100)
		];
	}

	public function get_menus()
	{
		return $this->db	->select('m.menu_id', 'm.name', 'm.title', 'COUNT(i.item_id) as nb_items')
						->from('menus m')
						->join('menus_items i', 'm.menu_id = i.menu_id', 'left')
						->group_by('m.menu_id')
						->order_by('m.title ASC')
						->get();
	}

	public function get_menu_choices()
	{
		$menus = [];

		foreach ($this->get_menus() as $menu)
		{
			$menus[$menu['menu_id']] = $menu['title'];
		}

		return $menus;
	}

	public function check_menu($menu_id, $name = '')
	{
		$this->db	->select('*')
					->from('menus')
					->where('menu_id', $menu_id);

		if ($name !== '')
		{
			$this->db->where('name', $name);
		}

		return $this->db->row();
	}

	public function name_exists($name, $exclude_menu_id = 0)
	{
		$query = $this->db	->from('menus')
							->where('name', $name);

		if ($exclude_menu_id)
		{
			$query->where('menu_id <>', (int)$exclude_menu_id);
		}

		return !$query->empty();
	}

	public function add_menu($name, $title)
	{
		return $this->db->insert('menus', [
			'name'  => $name ?: url_title($title),
			'title' => $title
		]);
	}

	public function edit_menu($menu_id, $name, $title)
	{
		$this->db	->where('menu_id', $menu_id)
					->update('menus', [
						'name'  => $name ?: url_title($title),
						'title' => $title
					]);
	}

	public function delete_menu($menu_id)
	{
		$this->db	->where('menu_id', $menu_id)
					->delete('menus');
	}

	public function get_menu_items($menu_id)
	{
		$items = $this->db	->select('i.*', 'p.title as parent_title')
						->from('menus_items i')
						->join('menus_items p', 'p.item_id = i.parent_id', 'left')
						->where('i.menu_id', $menu_id)
						->order_by('i.position ASC')
						->order_by('i.item_id ASC')
						->get(FALSE);

		return $this->flatten_items($items);
	}

	public function get_parent_items($menu_id, $exclude_item_id = 0)
	{
		$items = ['' => 'Aucun (niveau racine)'];
		$excluded = $exclude_item_id ? array_merge([$exclude_item_id], $this->descendant_ids($menu_id, $exclude_item_id)) : [];

		foreach ($this->get_menu_items($menu_id) as $item)
		{
			if ($excluded && in_array($item['item_id'], $excluded, TRUE))
			{
				continue;
			}

			// Parent level 3 would create a level 4 child, not allowed
			if ((int)$item['level'] >= self::MAX_SUBLEVELS)
			{
				continue;
			}

			$items[$item['item_id']] = str_repeat('- ', (int)$item['level']).$item['title'];
		}

		return $items;
	}

	public function next_position($menu_id)
	{
		$item = $this->db	->select('MAX(position) as max_position')
						->from('menus_items')
						->where('menu_id', $menu_id)
						->row(FALSE);

		return !empty($item['max_position']) ? ((int)$item['max_position'] + 1) : 1;
	}

	public function is_parent_depth_allowed($menu_id, $parent_id = NULL)
	{
		if (!$parent_id)
		{
			return TRUE;
		}

		foreach ($this->get_menu_items($menu_id) as $item)
		{
			if ((int)$item['item_id'] === (int)$parent_id)
			{
				return ((int)$item['level'] + 1) <= self::MAX_SUBLEVELS;
			}
		}

		return FALSE;
	}

	public function add_item($menu_id, $title, $url, $target, $parent_id, $position, $enabled)
	{
		$this->db->insert('menus_items', [
			'menu_id'   => $menu_id,
			'parent_id' => $parent_id ?: NULL,
			'title'     => $title,
			'url'       => trim($url),
			'target'    => $target ?: '_parent',
			'position'  => $position ?: $this->next_position($menu_id),
			'enabled'   => (bool)$enabled
		]);
	}

	public function check_item($item_id, $menu_id = NULL, $title_slug = '')
	{
		$this->db	->select('*')
					->from('menus_items')
					->where('item_id', $item_id);

		if ($menu_id !== NULL)
		{
			$this->db->where('menu_id', $menu_id);
		}

		$item = $this->db->row();

		if (!$item)
		{
			return FALSE;
		}

		if ($title_slug !== '' && url_title($item['title']) !== $title_slug)
		{
			return FALSE;
		}

		return $item;
	}

	public function edit_item($item_id, $title, $url, $target, $parent_id, $position, $enabled)
	{
		$this->db	->where('item_id', $item_id)
					->update('menus_items', [
						'parent_id' => $parent_id ?: NULL,
						'title'     => $title,
						'url'       => trim($url),
						'target'    => $target ?: '_parent',
						'position'  => $position ?: 0,
						'enabled'   => (bool)$enabled
					]);
	}

	public function sort_item($item_id, $position)
	{
		$item = $this->check_item($item_id);

		if (!$item)
		{
			return;
		}

		$items = $this->get_menu_items((int)$item['menu_id']);

		if (!$items)
		{
			return;
		}

		$current_index = NULL;

		foreach ($items as $index => $row)
		{
			if ((int)$row['item_id'] === (int)$item_id)
			{
				$current_index = $index;
				break;
			}
		}

		if ($current_index === NULL)
		{
			return;
		}

		$position = max(0, min((int)$position, count($items) - 1));

		if ($position === $current_index)
		{
			return;
		}

		$moved = $items[$current_index];
		array_splice($items, $current_index, 1);
		array_splice($items, $position, 0, [$moved]);

		foreach ($items as $order => $row)
		{
			$this->db	->where('item_id', $row['item_id'])
						->update('menus_items', [
							'position' => $order + 1
						]);
		}
	}

	public function sort_items($ordered_item_ids)
	{
		$ordered_item_ids = array_values(array_unique(array_map('intval', (array)$ordered_item_ids)));

		if (empty($ordered_item_ids))
		{
			return;
		}

		$first = $this->check_item($ordered_item_ids[0]);

		if (!$first)
		{
			return;
		}

		$menu_id = (int)$first['menu_id'];

		$rows = $this->db	->select('item_id')
						->from('menus_items')
						->where('menu_id', $menu_id)
						->order_by('position ASC')
						->order_by('item_id ASC')
						->get(FALSE);

		$menu_item_ids = array_map('intval', array_column($rows, 'item_id'));

		if (empty($menu_item_ids))
		{
			return;
		}

		$sorted = array_values(array_intersect($ordered_item_ids, $menu_item_ids));
		$missing = array_values(array_diff($menu_item_ids, $sorted));
		$final = array_merge($sorted, $missing);

		foreach ($final as $order => $item_id)
		{
			$this->db	->where('item_id', $item_id)
						->update('menus_items', [
							'position' => $order + 1
						]);
		}
	}

	public function delete_item($item_id)
	{
		$this->db	->where('item_id', $item_id)
					->delete('menus_items');
	}

	public function get_menu_links($menu_id)
	{
		$items = $this->db	->select('*')
						->from('menus_items')
						->where('menu_id', $menu_id)
						->where('enabled', TRUE)
						->order_by('position ASC')
						->order_by('item_id ASC')
						->get(FALSE);

		return $this->build_links_tree($items);
	}

	public function get_front_url_choices()
	{
		$urls = [];
		$modules = [];

		foreach (HiddenCMS()->model2('addon')->get('module') as $module)
		{
			if (!$module->is_enabled() || !$module->is_front())
			{
				continue;
			}

			$name = $module->info()->name;
			$title = (string)$module->info()->title;
			$base = !empty($module->info()->reserved_route) ? trim((string)$module->info()->reserved_route, '/') : trim((string)$name, '/');

			if ($base !== '')
			{
				$urls[$base] = '['.$title.'] '.$title;
			}

			$modules[$name] = [
				'title' => $title,
				'base'  => $base
			];
		}

		$lang = $this->config->lang->info()->name;

		if (isset($modules['pages']))
		{
			foreach ($this->db	->select('p.name', 'pl.title')
								->from('pages p')
								->join('pages_lang pl', 'p.page_id = pl.page_id')
								->where('pl.lang', $lang)
								->where('p.published', TRUE)
								->order_by('pl.title ASC')
								->get(FALSE) as $page)
			{
				$path = trim((string)$page['name'], '/');
				$path = $path === '' ? '/' : $path;
				$title = trim((string)$page['title']);
				$label = '[Pages] '.$title;

				$urls[$path] = $label;
			}
		}

		if (isset($modules['news']))
		{
			$base = $modules['news']['base'] ?: 'news';

			foreach ($this->db	->select('c.name', 'cl.title')
								->from('news_categories c')
								->join('news_categories_lang cl', 'c.category_id = cl.category_id')
								->where('cl.lang', $lang)
								->order_by('cl.title ASC')
								->get(FALSE) as $category)
			{
				$path = trim($base.'/'.$category['name'], '/');
				$label = '[News category] '.trim((string)$category['title']);

				$urls[$path] = $label;
			}

			foreach ($this->db	->select('c.name as category_name', 'nl.slug', 'nl.title')
								->from('news n')
								->join('news_categories c', 'c.category_id = n.category_id')
								->join('news_lang nl', 'nl.news_id = n.news_id')
								->where('nl.lang', $lang)
								->where('n.published', TRUE)
								->order_by('n.date DESC')
								->get(FALSE) as $news)
			{
				$path = trim($base.'/'.$news['category_name'].'/'.$news['slug'], '/');
				$label = '[News] '.trim((string)$news['title']);

				$urls[$path] = $label;
			}
		}

		natcasesort($urls);

		return $urls;
	}

	private function flatten_items($items)
	{
		$children = [];

		foreach ($items as $item)
		{
			$parent_id = $item['parent_id'] ?: 0;

			if (!isset($children[$parent_id]))
			{
				$children[$parent_id] = [];
			}

			$children[$parent_id][] = $item;
		}

		$output = [];

		$walk = function($parent_id = 0, $level = 0) use (&$walk, &$output, $children){
			if (empty($children[$parent_id]))
			{
				return;
			}

			foreach ($children[$parent_id] as $item)
			{
				$item['level'] = $level;
				$output[] = $item;

				$walk($item['item_id'], $level + 1);
			}
		};

		$walk();

		return $output;
	}

	private function build_links_tree($items)
	{
		$children = [];

		foreach ($items as $item)
		{
			$parent_id = $item['parent_id'] ?: 0;

			if (!isset($children[$parent_id]))
			{
				$children[$parent_id] = [];
			}

			$children[$parent_id][] = $item;
		}

		$build = function($parent_id = 0, $depth = 0) use (&$build, $children){
			$output = [];

			if (empty($children[$parent_id]))
			{
				return $output;
			}

			foreach ($children[$parent_id] as $item)
			{
				$link = [
					'title'  => $item['title'],
					'url'    => $item['url'],
					'target' => $item['target'],
					'icon'   => '',
					'access' => TRUE
				];

				$children_links = $depth < self::MAX_SUBLEVELS ? $build($item['item_id'], $depth + 1) : [];

				if ($children_links)
				{
					$link['url'] = $children_links;
				}

				$output[] = $link;
			}

			return $output;
		};

		return $build();
	}

	private function descendant_ids($menu_id, $item_id)
	{
		$rows = $this->db	->select('item_id', 'parent_id')
						->from('menus_items')
						->where('menu_id', $menu_id)
						->get(FALSE);

		$children = [];

		foreach ($rows as $row)
		{
			$parent_id = $row['parent_id'] ?: 0;

			if (!isset($children[$parent_id]))
			{
				$children[$parent_id] = [];
			}

			$children[$parent_id][] = $row['item_id'];
		}

		$descendants = [];
		$stack = [(int)$item_id];

		while ($stack)
		{
			$current = array_pop($stack);

			if (empty($children[$current]))
			{
				continue;
			}

			foreach ($children[$current] as $child_id)
			{
				if (!in_array($child_id, $descendants, TRUE))
				{
					$descendants[] = $child_id;
					$stack[] = $child_id;
				}
			}
		}

		return $descendants;
	}
}
