<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Menu;

use HB\HiddenCMS\Addons\Module;

class Menu extends Module
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('Menus'),
			'description' => '',
			'icon'        => 'fas fa-bars',
			'link'        => 'https://neofr.ag',
			'author'      => 'MichaÃ«l BILCOT & JÃ©rÃ©my VALENTIN <contact@HiddenCMS.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'admin'       => TRUE,
			'version'     => '1.0',
			'depends'     => [
				'HiddenCMS' => 'Alpha 0.2'
			],
			'routes'      => [
				'admin{pages}'                                       => 'index',
				'admin/add'                                          => 'add',
				'admin/{id}/{url_title}'                             => '_edit',
				'admin/delete/{id}/{url_title}'                      => 'delete',
				'admin/ajax/items/sort'                              => '_items_sort',
				'admin/ajax/add'                                      => 'add',
				'admin/ajax/{id}/{url_title}'                         => '_edit',
				'admin/ajax/delete/{id}/{url_title}'                  => 'delete',
				'admin/ajax/items/{id}/{url_title}/add'               => '_items_add',
				'admin/ajax/items/{id}/{url_title}/edit/{id}'         => '_items_edit',
				'admin/ajax/items/{id}/{url_title}/delete/{id}/{url_title}' => '_items_delete',
				'admin/items/{id}/{url_title}{pages}'                => '_items',
				'admin/items/{id}/{url_title}/add'                   => '_items_add',
				'admin/items/{id}/{url_title}/edit/{id}'             => '_items_edit',
				'admin/items/{id}/{url_title}/delete/{id}/{url_title}' => '_items_delete'
			]
		];
	}

	public function permissions()
	{
		return [
			'default' => [
				'access'  => [
					[
						'title'  => 'Menus',
						'icon'   => 'fas fa-bars',
						'access' => [
							'add_menus' => [
								'title' => 'Ajouter',
								'icon'  => 'fas fa-plus',
								'admin' => TRUE
							],
							'modify_menus' => [
								'title' => 'Modifier',
								'icon'  => 'fas fa-edit',
								'admin' => TRUE
							],
							'delete_menus' => [
								'title' => 'Supprimer',
								'icon'  => 'far fa-trash-alt',
								'admin' => TRUE
							]
						]
					]
				]
			],
			'link' => [
				'get_all' => function(){
					return HB()->db->select('item_id', 'CONCAT_WS(" ", "Lien", title)')->from('menus_items')->order_by('title')->get();
				},
				'check' => function($item_id){
					if (($title = HB()->db->select('title')->from('menus_items')->where('item_id', (int)$item_id)->row()) !== [])
					{
						return 'Lien '.$title;
					}
				},
				'init' => [
					'view_link' => [
						['visitors', TRUE]
					]
				],
				'access' => [
					[
						'title' => 'Liens',
						'icon' => 'fas fa-link',
						'access' => [
							'view_link' => [
								'title' => 'Visibilité',
								'icon' => 'far fa-eye'
							]
						]
					]
				]
			]
		];
	}
}
