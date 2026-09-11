<?php

namespace HB\Modules\Files\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Admin_Ajax extends Controller_Module
{
	private $image_extensions = ['avif', 'gif', 'jpeg', 'jpg', 'png', 'svg', 'webp'];

	public function picker()
	{
		$accept = isset($_GET['accept']) && $_GET['accept'] === 'image' ? 'image' : 'file';
		$files = [];

		foreach ($this->db->select('id', 'name', 'path', 'date')->from('file')->order_by('date DESC')->get(FALSE) as $file)
		{
			$path = $this->normalize_path($file['path']);

			if (strpos($path, 'upload/files/') !== 0 || !is_file(HIDDENCMS_CMS.'/'.$path))
			{
				continue;
			}

			$is_image = in_array(strtolower(extension($path)), $this->image_extensions, TRUE);

			if ($accept === 'image' && !$is_image)
			{
				continue;
			}

			$files[] = $this->file_data($file, $is_image);
		}

		return $this->json([
			'files'      => $files,
			'can_upload' => $this->is_authorized('add_files'),
			'max_size'   => human_size(file_upload_max_size())
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

		$accept = post('accept') === 'image' ? 'image' : 'file';
		$extension = strtolower(extension($_FILES['file']['name']));

		if ($accept === 'image' && !in_array($extension, $this->image_extensions, TRUE))
		{
			return $this->json(['error' => 'Veuillez choisir un fichier image.']);
		}

		if (!($file = HB()->model2('file')->static_uploaded_file($_FILES['file'], 'files')) || !$file->id)
		{
			return $this->json(['error' => 'Le fichier n’a pas pu être téléversé.']);
		}

		$this->ensure_access((int)$file->id);

		$row = $this->db->select('id', 'name', 'path', 'date')->from('file')->where('id', (int)$file->id)->row(FALSE);

		return $this->json([
			'file' => $this->file_data($row, in_array(strtolower(extension($row['path'])), $this->image_extensions, TRUE))
		]);
	}

	private function normalize_path($path)
	{
		$path = trim(str_replace('\\', '/', (string)$path));

		if (strpos($path, './') === 0)
		{
			$path = substr($path, 2);
		}

		return trim($path, '/');
	}

	private function file_data(array $file, $is_image)
	{
		$path = $this->normalize_path($file['path']);
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

	private function ensure_access($file_id)
	{
		if (!$this->db->select('access_id')->from('access')->where('module', 'files')->where('action', 'read_file')->where('id', $file_id)->row())
		{
			$this->access->init('files', 'file', $file_id);
		}
	}
}
