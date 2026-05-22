<?php
/**
 * https://neofr.ag
 * @author: HiddenCMS
 */

namespace HB\Modules\Files;

use HB\HiddenCMS\Addons\Module;

class Files extends Module
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('Fichiers'),
			'description' => '',
			'icon'        => 'far fa-folder-open',
			'link'        => 'https://neofr.ag',
			'author'      => 'HiddenCMS',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'admin'       => TRUE,
			'version'     => '1.0',
			'depends'     => [
				'HiddenCMS' => 'Alpha 0.2'
			],
			'routes'      => [
				'admin' => 'index'
			]
		];
	}

	public function permissions()
	{
		return [
			'default' => [
				'access'  => [
					[
						'title'  => 'Fichiers',
						'icon'   => 'far fa-folder-open',
						'access' => [
							'add_files' => [
								'title' => 'Ajouter',
								'icon'  => 'fas fa-plus',
								'admin' => TRUE
							],
							'modify_files' => [
								'title' => 'Modifier',
								'icon'  => 'fas fa-edit',
								'admin' => TRUE
							],
							'delete_files' => [
								'title' => 'Supprimer',
								'icon'  => 'far fa-trash-alt',
								'admin' => TRUE
							]
						]
					]
				]
			]
		];
	}
}
