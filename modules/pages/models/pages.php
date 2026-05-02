<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Pages\Models;

use HB\HiddenCMS\Loadables\Model;

class Pages extends Model
{
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

		for ($i = count($segments); $i > 0; $i--)
		{
			$name = implode('/', array_slice($segments, 0, $i));

			$this->db	->select('p.*', 'pl.title', 'pl.subtitle', 'pl.content')
						->from('pages p')
						->join('pages_lang pl', 'p.page_id = pl.page_id')
						->where('p.name', $name)
						->where('pl.lang', $lang);

			if (!$all)
			{
				$this->db->where('p.published', TRUE);
			}

			if ($page = $this->db->row())
			{
				return [
					'page'     => $page,
					'blocks'   => $this->get_blocks($page['page_id']),
					'segments' => array_slice($segments, $i)
				];
			}
		}

		return FALSE;
	}

	public function get_blocks($page_id, $region = 'content')
	{
		$blocks = [];

		foreach ($this->db	->select('*')
							->from('pages_instances')
							->where('page_id', $page_id)
							->where('region', $region)
							->where('enabled', TRUE)
							->order_by('position ASC')
							->get(FALSE) as $block)
		{
			$block['settings'] = $this->storage->decode($block['settings']);
			$blocks[] = $block;
		}

		return $blocks;
	}

	public function get_all_blocks($page_id)
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

	public function get_regions()
	{
		return HiddenCMS()->theme($this->config->default_theme)->regions();
	}

	public function get_page_modules()
	{
		$modules = [];

		foreach (HiddenCMS()->model2('addon')->get('module') as $module)
		{
			if ($module->is_enabled() && $module->is_front())
			{
				$modules[$module->info()->name] = [
					'title'  => (string)$module->info()->title,
					'blocks' => $this->normalize_page_blocks($module->page_blocks())
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
				'fields' => []
			];

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
					'values' => $values
				];
			}
		}

		return $output;
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

		foreach ($this->get_all_blocks($page_id) as $block)
		{
			if (!$block['module'])
			{
				$blocks[] = [
					'type'    => 'static',
					'region'  => !empty($block['region']) ? $block['region'] : 'content',
					'content' => isset($block['settings']['content']) ? $block['settings']['content'] : ''
				];

				continue;
			}

			$blocks[] = ['region' => !empty($block['region']) ? $block['region'] : 'content'] + $this->module_page_block_form_value($block);
		}

		return $this->storage->encode($blocks);
	}

	public function build_blocks($post)
	{
		$blocks = $this->storage->decode(isset($post['blocks']) ? $post['blocks'] : '', []);
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
						'region'   => !empty($block['region']) ? $block['region'] : 'content',
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
		return $this->db->select('p.page_id', 'p.name', 'p.published', 'pl.title', 'pl.subtitle')
						->from('pages p')
						->join('pages_lang pl', 'p.page_id = pl.page_id')
						->where('pl.lang', $this->config->lang->info()->name)
						->order_by('pl.title ASC')
						->get();
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

	public function add_page($name, $title, $published, $subtitle, $content, $blocks = [])
	{
		$page_id = $this->db->insert('pages', [
			'name'           => $name ?: url_title($title),
			'published'      => $published
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

	public function edit_page($page_id, $name, $title, $published, $subtitle, $content, $lang, $blocks = [])
	{
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
							'published'      => $published
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
							'published'      => $published
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

	public function save_blocks($page_id, $blocks = [])
	{
		$this->db	->where('page_id', $page_id)
					->delete('pages_instances');

		foreach (array_values($blocks) as $position => $block)
		{
			$this->db->insert('pages_instances', [
				'page_id'  => $page_id,
				'region'   => !empty($block['region']) ? $block['region'] : 'content',
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
		if (!($module = @HiddenCMS()->module($block['module'])) || !$module->is_enabled() || !$module->is_front())
		{
			return FALSE;
		}

		$type = !empty($block['block']) ? $block['block'] : 'index';
		$settings = !empty($block['settings']) && is_array($block['settings']) ? $block['settings'] : [];
		$data = $module->page_block($type, $settings);

		$data['settings'] = !empty($data['settings']) && is_array($data['settings']) ? $data['settings'] : [];
		$data['settings']['block'] = !empty($data['settings']['block']) ? $data['settings']['block'] : $type;

		return [
			'module'   => $block['module'],
			'region'   => !empty($block['region']) ? $block['region'] : 'content',
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
}


