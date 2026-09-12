<?php

namespace HB\Modules\Files\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Admin_Ajax extends Controller_Module
{
	private $image_extensions = ['avif', 'gif', 'jpeg', 'jpg', 'png', 'svg', 'webp'];

	public function picker()
	{
		$accept = isset($_GET['accept']) && in_array($_GET['accept'], ['image', 'gallery-image'], TRUE) ? $_GET['accept'] : 'file';
		$selected_id = isset($_GET['selected_id']) ? (int)$_GET['selected_id'] : 0;
		$dir = $this->normalize(isset($_GET['dir']) ? $_GET['dir'] : '');

		if (!isset($_GET['dir']) && $selected_id && ($selected = $this->file_record($selected_id)))
		{
			$dir = $this->file_dir($selected);
		}

		if ($dir === NULL || !is_dir($this->full_path($dir)))
		{
			$dir = '';
		}

		$directories = [];
		$full = $this->full_path($dir);

		foreach (scandir($full) as $entry)
		{
			if (in_array($entry, ['.', '..'], TRUE) || !is_dir($full.'/'.$entry))
			{
				continue;
			}

			$directories[] = [
				'name' => $entry,
				'path' => $dir.($dir !== '' ? '/' : '').$entry
			];
		}

		usort($directories, function($a, $b){
			return strnatcasecmp($a['name'], $b['name']);
		});

		$files = [];

		foreach ($this->db->select('id', 'name', 'path', 'date')->from('file')->order_by('name')->get(FALSE) as $file)
		{
			$path = $this->normalize_db_path($file['path']);

			if ($this->file_dir($file) !== $dir || strpos($path, 'upload/files/') !== 0 || !is_file(HIDDENCMS_CMS.'/'.$path))
			{
				continue;
			}

			$is_image = in_array(strtolower(extension($path)), $this->image_extensions, TRUE);

			if (($accept === 'image' && !$is_image) || ($accept === 'gallery-image' && !in_array(strtolower(extension($path)), ['jpeg', 'jpg', 'png'], TRUE)))
			{
				continue;
			}

			$files[] = $this->file_data($file, $is_image);
		}

		return $this->json([
			'current_dir' => $dir,
			'breadcrumbs' => $this->breadcrumbs($dir),
			'directories' => $directories,
			'files'       => $files,
			'can_upload'  => $this->is_authorized('add_files'),
			'can_mkdir'   => $this->is_authorized('add_files'),
			'max_size'    => human_size(file_upload_max_size())
		]);
	}

	public function picker_upload()
	{
		if (!$this->is_authorized('add_files'))
		{
			return $this->json(['error' => 'Vous n’êtes pas autorisé à ajouter des fichiers.']);
		}

		if (empty($_FILES['file']) || !empty($_FILES['file']['error']) || empty($_FILES['file']['tmp_name']))
		{
			return $this->json(['error' => 'Le fichier n’a pas pu être téléversé.']);
		}

		$dir = $this->normalize(post('dir'));

		if ($dir === NULL || !is_dir($this->full_path($dir)))
		{
			return $this->json(['error' => 'Le dossier de destination est invalide.']);
		}

		$accept = in_array(post('accept'), ['image', 'gallery-image'], TRUE) ? post('accept') : 'file';
		$extension = strtolower(extension($_FILES['file']['name']));

		if ($accept === 'image' && !in_array($extension, $this->image_extensions, TRUE))
		{
			return $this->json(['error' => 'Veuillez choisir un fichier image.']);
		}

		if ($accept === 'gallery-image' && !in_array($extension, ['jpeg', 'jpg', 'png'], TRUE))
		{
			return $this->json(['error' => 'Veuillez choisir une image JPEG ou PNG.']);
		}

		if (!($file = HB()->model2('file')->static_uploaded_file($_FILES['file'], $this->upload_dir($dir))) || !$file->id)
		{
			return $this->json(['error' => 'Le fichier n’a pas pu être téléversé.']);
		}

		$this->copy_access('read_directory', $this->directory_id($dir), 'read_file', (int)$file->id, 'file');

		$row = $this->file_record((int)$file->id);

		return $this->json([
			'file' => $this->file_data($row, in_array(strtolower(extension($row['path'])), $this->image_extensions, TRUE))
		]);
	}

	public function picker_mkdir()
	{
		if (!$this->is_authorized('add_files'))
		{
			return $this->json(['error' => 'Vous n’êtes pas autorisé à créer des dossiers.']);
		}

		$dir = $this->normalize(post('dir'));
		$name = $this->clean_name(post('name'));

		if ($dir === NULL || $name === '' || !is_dir($this->full_path($dir)))
		{
			return $this->json(['error' => 'Le nom ou le dossier de destination est invalide.']);
		}

		$path = $dir.($dir !== '' ? '/' : '').$name;
		$target = $this->full_path($path);

		if (!$target || file_exists($target))
		{
			return $this->json(['error' => 'Un dossier portant ce nom existe déjà.']);
		}

		dir_create($target);

		if (!is_dir($target))
		{
			return $this->json(['error' => 'Le dossier n’a pas pu être créé.']);
		}

		$this->copy_access('read_directory', $this->directory_id($dir), 'read_directory', $this->directory_id($path), 'directory');

		return $this->json([
			'folder' => [
				'name' => $name,
				'path' => $path
			]
		]);
	}

	private function root()
	{
		$root = HIDDENCMS_CMS.'/upload/files';
		dir_create($root);

		return str_replace('\\', '/', realpath($root));
	}

	private function normalize($path)
	{
		$path = trim(str_replace('\\', '/', (string)$path), '/');

		if ($path === '')
		{
			return '';
		}

		$parts = [];

		foreach (explode('/', $path) as $part)
		{
			$part = trim($part);

			if ($part === '' || $part === '.')
			{
				continue;
			}

			if ($part === '..')
			{
				return NULL;
			}

			$parts[] = $part;
		}

		return implode('/', $parts);
	}

	private function full_path($path)
	{
		$path = $this->normalize($path);

		if ($path === NULL)
		{
			return NULL;
		}

		$root = $this->root();
		$full = $root.($path !== '' ? '/'.$path : '');
		$check = file_exists($full) ? realpath($full) : realpath(dirname($full));

		if (!$check || stripos(str_replace('\\', '/', $check), $root) !== 0)
		{
			return NULL;
		}

		return $full;
	}

	private function clean_name($name)
	{
		$name = trim(str_replace(['/', '\\'], '', (string)$name));

		return in_array($name, ['', '.', '..'], TRUE) ? '' : $name;
	}

	private function upload_dir($dir)
	{
		return trim('files'.($dir !== '' ? '/'.$dir : ''), '/');
	}

	private function normalize_db_path($path)
	{
		$path = trim(str_replace('\\', '/', (string)$path));

		if (strpos($path, './') === 0)
		{
			$path = substr($path, 2);
		}

		return trim($path, '/');
	}

	private function file_relative_path($file)
	{
		$path = $this->normalize_db_path($file['path']);
		$prefix = 'upload/files/';

		return strpos($path, $prefix) === 0 ? substr($path, strlen($prefix)) : NULL;
	}

	private function file_dir($file)
	{
		$path = $this->file_relative_path($file);

		if ($path === NULL || strpos($path, '/') === FALSE)
		{
			return '';
		}

		return dirname($path);
	}

	private function file_record($id)
	{
		return $this->db->select('id', 'name', 'path', 'date')->from('file')->where('id', (int)$id)->row(FALSE);
	}

	private function breadcrumbs($dir)
	{
		$breadcrumbs = [['name' => 'Racine', 'path' => '']];
		$path = '';

		foreach (array_filter(explode('/', $dir)) as $part)
		{
			$path .= ($path !== '' ? '/' : '').$part;
			$breadcrumbs[] = ['name' => $part, 'path' => $path];
		}

		return $breadcrumbs;
	}

	private function file_data(array $file, $is_image)
	{
		$path = $this->normalize_db_path($file['path']);
		$full = HIDDENCMS_CMS.'/'.$path;

		return [
			'id'       => (int)$file['id'],
			'name'     => $file['name'],
			'url'      => url('files/'.pathinfo(basename($path), PATHINFO_FILENAME)),
			'is_image' => (bool)$is_image,
			'extension'=> strtoupper(extension($path)),
			'size'     => is_file($full) ? human_size(filesize($full)) : '',
			'date'     => !empty($file['date']) ? date('d/m/Y H:i', strtotime($file['date'])) : ''
		];
	}

	private function directory_id($dir)
	{
		if (($directory_id = $this->db->select('directory_id')->from('files_directories')->where('path', $dir)->row()))
		{
			return (int)$directory_id;
		}

		$directory_id = $this->db->insert('files_directories', ['path' => $dir]);
		$this->ensure_access('read_directory', $directory_id, 'directory');

		return (int)$directory_id;
	}

	private function ensure_access($action, $id, $type)
	{
		if (!$this->access_id($action, $id))
		{
			$this->access->init('files', $type, $id);
		}

		return $this;
	}

	private function access_id($action, $id)
	{
		return $this->db->select('access_id')->from('access')->where('module', 'files')->where('action', $action)->where('id', $id)->row();
	}

	private function copy_access($source_action, $source_id, $target_action, $target_id, $target_type)
	{
		$this->ensure_access($source_action, $source_id, 'directory');
		$this->ensure_access($target_action, $target_id, $target_type);

		$source_access_id = $this->access_id($source_action, $source_id);
		$target_access_id = $this->access_id($target_action, $target_id);

		if (!$source_access_id || !$target_access_id)
		{
			return $this;
		}

		$this->db->where('access_id', $target_access_id)->delete('access_details');

		foreach ($this->db->select('entity', 'type', 'authorized')->from('access_details')->where('access_id', $source_access_id)->get(FALSE) as $permission)
		{
			$this->db->insert('access_details', [
				'access_id'  => $target_access_id,
				'entity'     => $permission['entity'],
				'type'       => $permission['type'],
				'authorized' => $permission['authorized']
			]);
		}

		$this->access->reload();

		return $this;
	}
}
