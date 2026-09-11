<?php

namespace HB\Widgets\Image\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Widget as Controller_Widget;

class Index extends Controller_Widget
{
	public function index($settings = [])
	{
		$image = $this->image(isset($settings['image_id']) ? $settings['image_id'] : 0);

		if (!$image)
		{
			return '';
		}

		$this->css('image');

		return $this->panel()->body($this->view('index', [
			'image'    => $image,
			'settings' => $settings
		]));
	}

	private function image($id)
	{
		if ($path = $this->db->select('path')->from('file')->where('id', (int)$id)->row())
		{
			return url($path);
		}
	}
}
