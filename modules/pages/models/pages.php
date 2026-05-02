<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace NF\Modules\Pages\Models;

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
					'instance' => $this->get_instance($page['page_id']),
					'segments' => array_slice($segments, $i)
				];
			}
		}

		return FALSE;
	}

	public function get_instance($page_id)
	{
		$instance = $this->db	->select('*')
								->from('pages_instances')
								->where('page_id', $page_id)
								->where('enabled', TRUE)
								->order_by('position ASC')
								->row();

		if (!$instance)
		{
			return FALSE;
		}

		$instance['settings'] = $instance['settings'] ? @unserialize($instance['settings']) : [];

		return $instance;
	}

	public function get_page_modules()
	{
		$modules = ['' => $this->lang('Contenu statique')];

		foreach (HiddenCMS()->model2('addon')->get('module') as $module)
		{
			if ($module->is_enabled() && $module->is_front())
			{
				$modules[$module->info()->name] = $module->info()->title;
			}
		}

		array_natsort($modules);

		return $modules;
	}

	public function get_news_categories()
	{
		$categories = ['' => $this->lang('Toutes les actualitÃ©s')];

		foreach ($this->db	->select('c.category_id', 'cl.title')
							->from('news_categories c')
							->join('news_categories_lang cl', 'c.category_id = cl.category_id')
							->where('cl.lang', $this->config->lang->info()->name)
							->order_by('cl.title')
							->get() as $category)
		{
			$categories[$category['category_id']] = $category['title'];
		}

		return $categories;
	}

	public function get_instance_form_values($instance)
	{
		$values = [
			'module'        => '',
			'news_category' => ''
		];

		if ($instance)
		{
			$values['module'] = $instance['module'];

			if ($instance['module'] == 'news')
			{
				if (isset($instance['settings']['category_id']))
				{
					$values['news_category'] = $instance['settings']['category_id'];
				}
				else if (preg_match('#^category/([0-9]+)(?:/|$)#', $instance['route'], $match))
				{
					$values['news_category'] = $match[1];
				}
			}
		}

		return $values;
	}

	public function build_instance($post)
	{
		$module = isset($post['module']) ? $post['module'] : '';

		if (!$module)
		{
			return [
				'module'   => '',
				'route'    => '',
				'settings' => []
			];
		}

		$route = '';
		$settings = [];

		if ($module == 'news' && !empty($post['news_category']))
		{
			$category = $this->db	->select('c.category_id', 'c.name')
									->from('news_categories c')
									->where('c.category_id', $post['news_category'])
									->row();

			if ($category)
			{
				$route = 'category/'.$category['category_id'].'/'.$category['name'];
				$settings['category_id'] = $category['category_id'];
			}
		}

		return [
			'module'   => $module,
			'route'    => $route,
			'settings' => $settings
		];
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

	public function add_page($name, $title, $published, $subtitle, $content, $module = '', $route = '', $settings = [])
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

		$this->save_instance($page_id, $module, $route, $settings);
	}

	public function edit_page($page_id, $name, $title, $published, $subtitle, $content, $lang, $module = '', $route = '', $settings = [])
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

		$this->save_instance($page_id, $module, $route, $settings);
	}

	public function delete_page($page_id)
	{
		$this->db	->where('page_id', $page_id)
					->delete('pages');

		$this->access->delete('pages', $page_id);
	}

	public function save_instance($page_id, $module = '', $route = '', $settings = [])
	{
		if (!$module)
		{
			$this->db	->where('page_id', $page_id)
						->delete('pages_instances');

			return $this;
		}

		$data = [
			'page_id'  => $page_id,
			'module'   => $module,
			'route'    => trim($route, '/'),
			'settings' => serialize($settings),
			'position' => 0,
			'enabled'  => TRUE
		];

		if ($this->db->from('pages_instances')->where('page_id', $page_id)->empty())
		{
			$this->db->insert('pages_instances', $data);
		}
		else
		{
			$this->db	->where('page_id', $page_id)
						->update('pages_instances', $data);
		}

		return $this;
	}
}


