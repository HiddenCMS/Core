<?php

namespace HB\Widgets\Hero\Controllers;

use HB\HiddenCMS\Loadables\Controller;

class Admin extends Controller
{
	public function index($settings = [])
	{
		return $this->view('admin', [
			'images'   => $this->images(),
			'settings' => $settings
		]);
	}

	private function images()
	{
		$images = [];

		foreach ($this->db->select('id', 'name', 'path')->from('file')->order_by('name')->get(FALSE) as $file)
		{
			if (in_array(strtolower(extension($file['path'])), ['avif', 'gif', 'jpeg', 'jpg', 'png', 'svg', 'webp'], TRUE))
			{
				$images[$file['id']] = $file['name'];
			}
		}

		return $images;
	}
}
