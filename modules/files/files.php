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
			'front'       => TRUE,
			'version'     => '1.0',
			'reserved_route' => 'files',
			'depends'     => [
				'HiddenCMS' => 'Alpha 0.2'
			],
			'routes'      => [
				'{url_title}' => '_file',
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
			],
			'directory' => [
				'get_all' => function(){
					return HiddenCMS()->db->select('directory_id', 'CONCAT_WS(" ", "Dossier", path)')->from('files_directories')->order_by('path')->get();
				},
				'check' => function($directory_id){
					if (($path = HiddenCMS()->db->select('path')->from('files_directories')->where('directory_id', (int)$directory_id)->row()) !== [])
					{
						return 'Dossier '.$path;
					}
				},
				'init' => [
					'read_directory' => [
						['visitors', TRUE]
					]
				],
				'access' => [
					[
						'title'  => 'Dossiers',
						'icon'   => 'far fa-folder',
						'access' => [
							'read_directory' => [
								'title' => 'Lecture',
								'icon'  => 'far fa-eye'
							]
						]
					]
				]
			],
			'file' => [
				'get_all' => function(){
					return HiddenCMS()->db->select('id', 'CONCAT_WS(" ", "Fichier", name)')->from('file')->where('path LIKE', 'upload/files/%')->order_by('name')->get();
				},
				'check' => function($file_id){
					if (($name = HiddenCMS()->db->select('name')->from('file')->where('id', (int)$file_id)->where('path LIKE', 'upload/files/%')->row()) !== [])
					{
						return 'Fichier '.$name;
					}
				},
				'init' => [
					'read_file' => [
						['visitors', TRUE]
					]
				],
				'access' => [
					[
						'title'  => 'Fichiers',
						'icon'   => 'far fa-file',
						'access' => [
							'read_file' => [
								'title' => 'Lecture',
								'icon'  => 'far fa-eye'
							]
						]
					]
				]
			]
		];
	}
}
