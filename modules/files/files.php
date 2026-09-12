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
				'admin/ajax/picker/mkdir' => 'picker_mkdir',
				'admin/ajax/picker/upload' => 'picker_upload',
				'admin/ajax/picker' => 'picker',
				'{url_title}' => '_file',
				'admin' => 'index'
			]
		];
	}

	public function picker_field($name, $file_id = 0, $label = 'Fichier', $accept = 'file', $empty_label = 'Aucun fichier sélectionné')
	{
		$file = [];

		if ($file_id)
		{
			$file = $this->db->select('id', 'name', 'path')->from('file')->where('id', (int)$file_id)->row(FALSE);
		}

		$is_image = $file && in_array(strtolower(extension($file['path'])), ['avif', 'gif', 'jpeg', 'jpg', 'png', 'svg', 'webp'], TRUE);
		$url = $file ? url($file['path']) : '';

		return $this->view('picker_field', [
			'name'        => $name,
			'label'       => $label,
			'accept'      => $accept,
			'empty_label' => $empty_label,
			'file'        => $file,
			'is_image'    => $is_image,
			'url'         => $url
		]);
	}

	public function picker_directory_field($name, $path = '', $label = 'Dossier', $accept = 'gallery-image', $empty_label = 'Aucun dossier sélectionné')
	{
		$path = trim(str_replace('\\', '/', (string)$path), '/');
		$selected = $path !== '';

		return '<link rel="stylesheet" href="'.css('file_picker.css').'" />'
			.'<script type="text/javascript" src="'.js('file_picker.js').'"></script>'
			.'<div class="field files-picker-field" data-file-picker data-picker-mode="directory" data-accept="'.utf8_htmlentities($accept).'">'
			.'<label>'.$this->lang($label).'</label>'
			.'<input type="hidden" name="'.utf8_htmlentities($name).'" value="'.utf8_htmlentities($path).'" />'
			.'<div class="files-picker-selection'.($selected ? ' has-file' : '').'">'
			.'<div class="files-picker-selection-preview">'.icon('far fa-folder-open').'</div>'
			.'<div class="files-picker-selection-details"><strong data-file-picker-name>'.($selected ? utf8_htmlentities($path) : $this->lang($empty_label)).'</strong><small>'.$this->lang('Les images JPEG et PNG de ce dossier alimenteront automatiquement la galerie.').'</small></div>'
			.'<div class="files-picker-selection-actions"><button type="button" class="ui primary button" data-file-picker-open>'.icon('far fa-folder-open').' '.$this->lang('Parcourir').'</button>'
			.'<button type="button" class="ui icon button" data-file-picker-clear title="'.$this->lang('Retirer').'" aria-label="'.$this->lang('Retirer').'"'.(!$selected ? ' style="display:none"' : '').'>'.icon('fas fa-times').'</button></div>'
			.'</div></div>';
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
