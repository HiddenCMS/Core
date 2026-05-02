<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\News;

use HB\HiddenCMS\Addons\Module;

class News extends Module
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('ActualitÃ©s'),
			'description' => '',
			'icon'        => 'far fa-file-alt',
			'link'        => 'https://neofr.ag',
			'author'      => 'MichaÃ«l BILCOT & JÃ©rÃ©my VALENTIN <contact@HiddenCMS.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'admin'       => TRUE,
			'front'       => TRUE,
			'version'     => '1.0',
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
										->title('ActualitÃ©s par page')
										->value($this->config->news_per_page)
							)
							->success(function($data){
								$this->config('news_per_page', $data['news_per_page']);
								notify('Configuration modifiÃ©e');
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
						'title'  => 'ActualitÃ©s',
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
						'title'  => 'CatÃ©gories',
						'icon'   => 'fas fa-align-left',
						'access' => [
							'add_news_category' => [
								'title' => 'Ajouter une catÃ©gorie',
								'icon'  => 'fas fa-plus',
								'admin' => TRUE
							],
							'modify_news_category' => [
								'title' => 'Modifier une catÃ©gorie',
								'icon'  => 'fas fa-edit',
								'admin' => TRUE
							],
							'delete_news_category' => [
								'title' => 'Supprimer une catÃ©gorie',
								'icon'  => 'far fa-trash-alt',
								'admin' => TRUE
							]
						]
					]
				]
			]
		];
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
				'url'   => 'news/'.$news_id.'/'.url_title($news)
			];
		}
	}
}


