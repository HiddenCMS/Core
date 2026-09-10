<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Pages\Models;

use HB\HiddenCMS\Loadables\Model;

class Pages extends Model
{
	public function normalize_page_name($name, $title = '')
	{
		return trim(url_title($name ?: $title), '/');
	}

	public function is_reserved_page_name($name)
	{
		$segments = explode('/', trim((string)$name, '/'));
		$first = isset($segments[0]) ? url_title($segments[0]) : '';

		if ($first === '')
		{
			return FALSE;
		}

		return in_array($first, $this->reserved_route_segments(), TRUE);
	}

	private function reserved_route_segments()
	{
		$reserved = [];

		foreach (HiddenCMS()->model2('addon')->get('module') as $module)
		{
			if (!$module->is_enabled() || !$module->is_front() || empty($module->info()->reserved_route))
			{
				continue;
			}

			$segments = explode('/', trim((string)$module->info()->reserved_route, '/'));
			$first = isset($segments[0]) ? url_title($segments[0]) : '';

			if ($first !== '')
			{
				$reserved[] = $first;
			}
		}

		return array_values(array_unique($reserved));
	}

	public function resolve($segments, $lang = 'default', $all = FALSE)
	{
		if ($lang == 'default')
		{
			$lang = $this->config->lang->info()->name;
		}

		$segments = array_values(array_filter($segments, function($segment){
			return $segment !== '';
		}));

		if (!$segments)
		{
			$segments = explode('/', $this->config->default_page);
		}

		$parent_id = 0;
		$page = FALSE;
		$matched = 0;

		foreach ($segments as $segment)
		{
			$this->db	->select('p.*', 'pl.title', 'pl.subtitle', 'pl.content')
						->from('pages p')
						->join('pages_lang pl', 'p.page_id = pl.page_id')
						->where('p.name', $segment)
						->where('p.parent_id', $parent_id)
						->where('pl.lang', $lang);

			if (!$all)
			{
				$this->db->where('p.published', TRUE);
			}

			if (!($candidate = $this->db->row()))
			{
				break;
			}

			$page = $candidate;
			$parent_id = (int)$page['page_id'];
			$matched++;
		}

		if ($page)
		{
			$page['path'] = implode('/', array_slice($segments, 0, $matched));

			return [
				'page'     => $page,
				'blocks'   => $this->get_blocks($page['page_id']),
				'segments' => array_slice($segments, $matched)
			];
		}

		return FALSE;
	}

	public function get_blocks($page_id)
	{
		$blocks = [];

		foreach ($this->db	->select('*')
							->from('pages_instances')
							->where('page_id', $page_id)
							->where('enabled', TRUE)
							->order_by('position ASC')
							->get(FALSE) as $block)
		{
			$block['settings'] = $this->storage->decode($block['settings']);
			$blocks[] = $block;
		}

		return $blocks;
	}

	public function get_page_modules()
	{
		$modules = [];

		foreach (HiddenCMS()->model2('addon')->get('module') as $module)
		{
			if ($module->is_enabled() && $module->is_front() && !empty($module->info()->page_blocks))
			{
				$blocks = $this->normalize_page_blocks($module->page_blocks());

				if (!$blocks)
				{
					continue;
				}

				$modules[$module->info()->name] = [
					'title'  => (string)$module->info()->title,
					'icon'   => !empty($module->info()->icon) ? (string)$module->info()->icon : 'fas fa-cube',
					'blocks' => $blocks
				];
			}
		}

		uasort($modules, function($a, $b){
			return strnatcasecmp($a['title'], $b['title']);
		});

		return $modules;
	}

	private function normalize_page_blocks($blocks)
	{
		$output = [];

		foreach ($blocks as $name => $block)
		{
			$output[$name] = [
				'title'  => !empty($block['title']) ? (string)$block['title'] : $name,
				'icon'   => !empty($block['icon']) ? (string)$block['icon'] : 'fas fa-layer-group',
				'displays' => [],
				'fields' => []
			];

			foreach (!empty($block['displays']) && is_array($block['displays']) ? $block['displays'] : [] as $display => $settings)
			{
				if (!is_array($settings))
				{
					$settings = ['title' => $settings];
				}

				$output[$name]['displays'][$display] = [
					'title' => !empty($settings['title']) ? (string)$settings['title'] : $display,
					'icon'  => !empty($settings['icon']) ? (string)$settings['icon'] : 'fas fa-th-large'
				];
			}

			foreach (!empty($block['fields']) && is_array($block['fields']) ? $block['fields'] : [] as $field => $settings)
			{
				$values = [];

				foreach (!empty($settings['values']) && is_array($settings['values']) ? $settings['values'] : [] as $value => $label)
				{
					$values[$value] = (string)$label;
				}

				$output[$name]['fields'][$field] = [
					'label'  => !empty($settings['label']) ? (string)$settings['label'] : $field,
					'type'   => !empty($settings['type']) ? (string)$settings['type'] : 'text',
					'values' => $values,
					'default' => isset($settings['default']) ? $settings['default'] : '',
					'min'     => isset($settings['min']) ? $settings['min'] : NULL,
					'max'     => isset($settings['max']) ? $settings['max'] : NULL,
					'step'    => isset($settings['step']) ? $settings['step'] : NULL
				];
			}
		}

		return $output;
	}

	public function get_outlines()
	{
		if (($module = @HiddenCMS()->module('outlines')) && $module->is_enabled())
		{
			return $module->model2('outline')->get_outline_choices();
		}

		return [];
	}

	public function get_blocks_form_value($page_id, $content = '')
	{
		$blocks = [];

		if ($content !== '')
		{
			$blocks[] = [
				'type'    => 'static',
				'content' => $content
			];
		}

		foreach ($this->get_blocks($page_id) as $block)
		{
			if (!$block['module'])
			{
				$blocks[] = [
					'type'    => 'static',
					'content' => isset($block['settings']['content']) ? $block['settings']['content'] : ''
				];

				continue;
			}

			$blocks[] = $this->module_page_block_form_value($block);
		}

		return $this->storage->encode($blocks);
	}

	public function build_blocks($post)
	{
		$blocks = $this->storage->decode(utf8_html_entity_decode(isset($post['blocks']) ? $post['blocks'] : '', ENT_QUOTES), []);
		$output = [];

		if (!is_array($blocks))
		{
			return $output;
		}

		foreach ($blocks as $block)
		{
			if (!is_array($block) || empty($block['type']))
			{
				continue;
			}

			if ($block['type'] == 'static')
			{
				$content = isset($block['content']) ? trim($block['content']) : '';

				if ($content !== '')
				{
					$output[] = [
						'module'   => '',
						'route'    => '',
						'settings' => [
							'type'    => 'static',
							'content' => $content
						]
					];
				}

				continue;
			}

			if ($block['type'] == 'module' && !empty($block['module']))
			{
				if ($module_block = $this->build_module_block($block))
				{
					$output[] = $module_block;
				}
			}
		}

		return $output;
	}

	public function get_pages()
	{
		$pages = $this->db->select('p.page_id', 'p.parent_id', 'p.name', 'p.published', 'pl.title', 'pl.subtitle')
						->from('pages p')
						->join('pages_lang pl', 'p.page_id = pl.page_id')
						->where('pl.lang', $this->config->lang->info()->name)
						->get(FALSE);

		$pages = $this->add_paths($pages);

		usort($pages, function($a, $b){
			return strnatcasecmp($a['path'], $b['path']);
		});

		return $pages;
	}

	public function get_parent_choices($exclude_page_id = 0)
	{
		$choices = [0 => $this->lang('Aucune (page racine)')];
		$pages = $this->add_paths($this->db
									->select('p.page_id', 'p.parent_id', 'p.name', 'pl.title')
									->from('pages p')
									->join('pages_lang pl', 'p.page_id = pl.page_id')
									->where('pl.lang', $this->config->lang->info()->name)
									->get(FALSE));

		usort($pages, function($a, $b){
			return strnatcasecmp($a['path'], $b['path']);
		});

		foreach ($pages as $page)
		{
			if ($exclude_page_id && ($page['page_id'] == $exclude_page_id || $this->is_descendant($page['page_id'], $exclude_page_id, $pages)))
			{
				continue;
			}

			$choices[$page['page_id']] = str_repeat('— ', $page['depth']).$page['title'].'  /'.$page['path'];
		}

		return $choices;
	}

	public function get_page_path($page_id, $lang = 'default')
	{
		if ($lang == 'default')
		{
			$lang = $this->config->lang->info()->name;
		}

		foreach ($this->add_paths($this->db
									->select('p.page_id', 'p.parent_id', 'p.name')
									->from('pages p')
									->get(FALSE)) as $page)
		{
			if ((int)$page['page_id'] === (int)$page_id)
			{
				return $page['path'];
			}
		}

		return '';
	}

	public function get_breadcrumbs($page_id)
	{
		$pages = [];

		foreach ($this->get_pages() as $page)
		{
			$pages[(int)$page['page_id']] = $page;
		}

		$breadcrumbs = [];
		$current_id = (int)$page_id;
		$visited = [];

		while (isset($pages[$current_id]) && !isset($visited[$current_id]))
		{
			$visited[$current_id] = TRUE;
			$page = $pages[$current_id];
			array_unshift($breadcrumbs, [
				'title' => $page['title'],
				'path'  => $page['path']
			]);
			$current_id = (int)$page['parent_id'];
		}

		return $breadcrumbs;
	}

	public function name_exists($name, $parent_id = 0, $exclude_page_id = 0)
	{
		$query = $this->db->from('pages')
						  ->where('name', $name)
						  ->where('parent_id', (int)$parent_id);

		if ($exclude_page_id)
		{
			$query->where('page_id <>', (int)$exclude_page_id);
		}

		return !$query->empty();
	}

	public function valid_parent($page_id, $parent_id)
	{
		$page_id = (int)$page_id;
		$parent_id = (int)$parent_id;

		if (!$parent_id)
		{
			return TRUE;
		}

		$pages = $this->db->select('page_id', 'parent_id')->from('pages')->get(FALSE);

		if ($page_id && ($parent_id === $page_id || $this->is_descendant($parent_id, $page_id, $pages)))
		{
			return FALSE;
		}

		foreach ($pages as $page)
		{
			if ((int)$page['page_id'] === $parent_id)
			{
				return TRUE;
			}
		}

		return FALSE;
	}

	public function check_page($page_id, $title, $lang = 'default', $all = FALSE)
	{
		if ($lang == 'default')
		{
			$lang = $this->config->lang->info()->name;
		}

		$this->db	->select('p.*', 'pl.title', 'pl.subtitle', 'pl.content')
					->from('pages p')
					->join('pages_lang pl', 'p.page_id = pl.page_id')
					->where('p.page_id', $page_id);

		if (!$all)
		{
			$this->db->where('p.published', TRUE);
		}

		$page = $this->db	->where('pl.lang', $lang)
							->row();

		if ($page && url_title($page['title']) == $title)
		{
			return $page;
		}
		else
		{
			return FALSE;
		}
	}

	public function add_page($name, $title, $published, $outline_id, $subtitle, $content, $blocks = [], $parent_id = 0)
	{
		if (!$this->valid_parent(0, $parent_id))
		{
			throw new \InvalidArgumentException('Page parente invalide.');
		}

		$page_id = $this->db->insert('pages', [
			'name'           => $name ?: url_title($title),
			'published'      => $published,
			'outline_id'     => $outline_id ?: NULL,
			'parent_id'      => (int)$parent_id
		]);

		$this->db->insert('pages_lang', [
			'page_id'        => $page_id,
			'lang'           => $this->config->lang->info()->name,
			'title'          => $title,
			'subtitle'       => $subtitle,
			'content'        => $content
		]);

		$this->access->init('pages', 'page', $page_id);

		$this->save_blocks($page_id, $blocks);
	}

	public function edit_page($page_id, $name, $title, $published, $outline_id, $subtitle, $content, $lang, $blocks = [], $parent_id = 0)
	{
		if (!$this->valid_parent($page_id, $parent_id))
		{
			throw new \InvalidArgumentException('Page parente invalide.');
		}

		if (!$this->db	->from('pages p')
						->join('pages_lang l', 'p.page_id = l.page_id')
						->where('p.page_id', $page_id)
						->where('l.lang', $lang)
						->empty())
		{
			$this->db	->where('page_id', $page_id)
						->where('lang', $lang)
						->update('pages_lang', [
							'title'    => $title,
							'subtitle' => $subtitle,
							'content'  => $content
						]);

			$this->db	->where('page_id', $page_id)
						->update('pages', [
							'name'           => $name ?: url_title($title),
							'published'      => $published,
							'outline_id'     => $outline_id ?: NULL,
							'parent_id'      => (int)$parent_id
						]);
		}
		else
		{
			$this->db	->insert('pages_lang', [
							'page_id'  => $page_id,
							'lang'     => $lang,
							'title'    => $title,
							'subtitle' => $subtitle,
							'content'  => $content
						]);

			$this->db	->where('page_id', $page_id)
						->update('pages', [
							'name'           => $name ?: url_title($title),
							'published'      => $published,
							'outline_id'     => $outline_id ?: NULL,
							'parent_id'      => (int)$parent_id
						]);
		}

		$this->save_blocks($page_id, $blocks);
	}

	public function delete_page($page_id)
	{
		$this->db	->where('page_id', $page_id)
					->delete('pages');

		$this->access->delete('pages', $page_id);
	}

	public function has_children($page_id)
	{
		return !$this->db->from('pages')->where('parent_id', (int)$page_id)->empty();
	}

	public function save_blocks($page_id, $blocks = [])
	{
		$this->db	->where('page_id', $page_id)
					->delete('pages_instances');

		foreach (array_values($blocks) as $position => $block)
		{
			$this->db->insert('pages_instances', [
				'page_id'  => $page_id,
				'module'   => isset($block['module']) ? $block['module'] : '',
				'route'    => isset($block['route']) ? trim($block['route'], '/') : '',
				'settings' => $this->storage->encode(isset($block['settings']) ? $block['settings'] : []),
				'position' => $position,
				'enabled'  => TRUE
			]);
		}

		return $this;
	}

	private function build_module_block($block)
	{
		if (!($module = @HiddenCMS()->module($block['module'])) || !$module->is_enabled() || !$module->is_front() || empty($module->info()->page_blocks))
		{
			return FALSE;
		}

		$type = !empty($block['block']) ? $block['block'] : 'index';
		$available = $module->page_blocks();

		if (!is_array($available) || !isset($available[$type]))
		{
			return FALSE;
		}

		$settings = !empty($block['settings']) && is_array($block['settings']) ? $block['settings'] : [];
		$data = $module->page_block($type, $settings);

		$data['settings'] = !empty($data['settings']) && is_array($data['settings']) ? $data['settings'] : [];
		$data['settings']['block'] = !empty($data['settings']['block']) ? $data['settings']['block'] : $type;

		return [
			'module'   => $block['module'],
			'route'    => !empty($data['route']) ? $data['route'] : '',
			'settings' => $data['settings']
		];
	}

	private function module_page_block_form_value($block)
	{
		if (($module = @HiddenCMS()->module($block['module'])) && $module->is_enabled() && $module->is_front())
		{
			return $module->page_block_form_value($block);
		}

		return [
			'type'     => 'module',
			'module'   => $block['module'],
			'block'    => !empty($block['settings']['block']) ? $block['settings']['block'] : 'index',
			'settings' => !empty($block['settings']) && is_array($block['settings']) ? $block['settings'] : []
		];
	}

	private function add_paths($pages)
	{
		$by_id = [];

		foreach ($pages as $page)
		{
			$by_id[(int)$page['page_id']] = $page;
		}

		$build = function($page_id, $visited = []) use (&$build, $by_id){
			if (!isset($by_id[$page_id]) || isset($visited[$page_id]))
			{
				return ['', 0];
			}

			$page = $by_id[$page_id];
			$parent_id = (int)$page['parent_id'];

			if (!$parent_id || !isset($by_id[$parent_id]))
			{
				return [$page['name'], 0];
			}

			$visited[$page_id] = TRUE;
			list($parent_path, $depth) = $build($parent_id, $visited);

			return [trim($parent_path.'/'.$page['name'], '/'), $depth + 1];
		};

		foreach ($pages as &$page)
		{
			list($page['path'], $page['depth']) = $build((int)$page['page_id']);
		}
		unset($page);

		return $pages;
	}

	private function is_descendant($page_id, $ancestor_id, $pages)
	{
		$parents = [];

		foreach ($pages as $page)
		{
			$parents[(int)$page['page_id']] = (int)$page['parent_id'];
		}

		$visited = [];
		$current = (int)$page_id;

		while (isset($parents[$current]) && $parents[$current] && !isset($visited[$current]))
		{
			$visited[$current] = TRUE;
			$current = $parents[$current];

			if ($current === (int)$ancestor_id)
			{
				return TRUE;
			}
		}

		return FALSE;
	}
}


