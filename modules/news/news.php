<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\News;

use HB\HiddenCMS\Addons\Module;

class News extends Module
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('Actualités'),
			'description' => '',
			'icon'        => 'far fa-file-alt',
			'link'        => 'https://neofr.ag',
			'author'      => 'Michaël BILCOT & Jérémy VALENTIN <contact@HiddenCMS.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'admin'       => TRUE,
			'front'       => TRUE,
			'version'     => '1.0',
			'reserved_route' => 'news',
			'depends'     => [
				'HiddenCMS' => 'Alpha 0.2'
			],
			'routes'      => [
				//Index
				'{page}'                                   => 'index',
				'{id}/{url_title}'                         => '_news',
				'tag/{url_title}{pages}'                   => '_tag',
				'category/{id}/{url_title}{pages}'         => '_category',

				//Admin
				'admin{pages}'                             => 'index',
				'admin/{id}/{url_title}'                   => '_edit',
				'admin/categories/add'                     => '_categories_add',
				'admin/categories/{id}/{url_title}'        => '_categories_edit',
				'admin/categories/delete/{id}/{url_title}' => '_categories_delete'
			],
			'settings'    => function(){
				return $this->form2()
							->rule($this->form_number('news_per_page')
										->title('Actualités par page')
										->value($this->config->news_per_page)
							)
							->success(function($data){
								$this->config('news_per_page', $data['news_per_page']);
								notify('Configuration modifiée');
								refresh();
							});
			}
		];
	}

	public function permissions()
	{
		return [
			'default' => [
				'access'  => [
					[
						'title'  => 'Actualités',
						'icon'   => 'far fa-file-alt',
						'access' => [
							'add_news' => [
								'title' => 'Ajouter',
								'icon'  => 'fas fa-plus',
								'admin' => TRUE
							],
							'modify_news' => [
								'title' => 'Modifier',
								'icon'  => 'fas fa-edit',
								'admin' => TRUE
							],
							'delete_news' => [
								'title' => 'Supprimer',
								'icon'  => 'far fa-trash-alt',
								'admin' => TRUE
							]
						]
					],
					[
						'title'  => 'Catégories',
						'icon'   => 'fas fa-align-left',
						'access' => [
							'add_news_category' => [
								'title' => 'Ajouter une catégorie',
								'icon'  => 'fas fa-plus',
								'admin' => TRUE
							],
							'modify_news_category' => [
								'title' => 'Modifier une catégorie',
								'icon'  => 'fas fa-edit',
								'admin' => TRUE
							],
							'delete_news_category' => [
								'title' => 'Supprimer une catégorie',
								'icon'  => 'far fa-trash-alt',
								'admin' => TRUE
							]
						]
					]
				]
			]
		];
	}

	public function page_blocks()
	{
		return [
			'index' => [
				'title'  => (string)$this->lang('Toutes les actualités'),
				'fields' => []
			],
			'category' => [
				'title'  => (string)$this->lang('Catégorie d\'actualités'),
				'fields' => [
					'category_id' => [
						'label'  => (string)$this->lang('Catégorie'),
						'type'   => 'select',
						'values' => $this->page_block_categories()
					]
				]
			]
		];
	}

	public function page_block($block = 'index', $settings = [])
	{
		if ($block == 'category' && !empty($settings['category_id']))
		{
			$category = $this->db	->select('category_id', 'name')
									->from('news_categories')
									->where('category_id', $settings['category_id'])
									->row();

			if ($category)
			{
				return [
					'route'    => 'category/'.$category['category_id'].'/'.$category['name'],
					'settings' => [
						'block'       => 'category',
						'category_id' => $category['category_id']
					]
				];
			}
		}

		return [
			'route'    => '',
			'settings' => [
				'block' => 'index'
			]
		];
	}

	public function page_block_form_value($block)
	{
		$settings = !empty($block['settings']) && is_array($block['settings']) ? $block['settings'] : [];
		$type = !empty($settings['block']) ? $settings['block'] : (!empty($settings['category_id']) ? 'category' : 'index');

		return [
			'type'     => 'module',
			'module'   => $this->info()->name,
			'block'    => $type,
			'settings' => [
				'category_id' => isset($settings['category_id']) ? $settings['category_id'] : ''
			]
		];
	}

	private function page_block_categories()
	{
		$categories = ['' => (string)$this->lang('Toutes les actualités')];

		foreach ($this->db	->select('c.category_id', 'cl.title')
							->from('news_categories c')
							->join('news_categories_lang cl', 'c.category_id = cl.category_id')
							->where('cl.lang', $this->config->lang->info()->name)
							->order_by('cl.title')
							->get() as $category)
		{
			$categories[$category['category_id']] = (string)$category['title'];
		}

		return $categories;
	}

	public function comments($news_id)
	{
		$news = $this->db	->select('title')
							->from('news_lang')
							->where('news_id', $news_id)
							->where('lang', $this->config->lang->info()->name)
							->row();

		if ($news)
		{
			return [
				'title' => $news,
				'url'   => $this->news_path($news_id, $news)
			];
		}
	}

	public function news_path($news_id, $title)
	{
		return $this->base_path().$news_id.'/'.url_title($title);
	}

	public function category_path($category_id, $category_name)
	{
		return $this->base_path().'category/'.$category_id.'/'.url_title($category_name);
	}

	public function tag_path($tag)
	{
		return $this->base_path().'tag/'.url_title($tag);
	}

	public function index_path()
	{
		return rtrim($this->base_path(), '/');
	}

	private function base_path()
	{
		if (!$this->url->admin && !$this->url->ajax && !empty($this->info()->reserved_route))
		{
			return trim($this->info()->reserved_route, '/').'/';
		}

		if (!$this->url->admin && !$this->url->ajax && ($page = $this->output->data->get('page', 'name')))
		{
			return trim($page, '/').'/';
		}

		return $this->info()->name.'/';
	}
}


