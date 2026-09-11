<?php

namespace HB\Widgets\Media_Text\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Widget as Controller_Widget;

class Index extends Controller_Widget
{
	public function index($settings = [])
	{
		$image = $this->db->select('path')->from('file')->where('id', isset($settings['image_id']) ? (int)$settings['image_id'] : 0)->row();
		$this->css('media-text');

		return $this->panel()->body($this->view('index', [
			'image'    => $image ? url($image) : '',
			'settings' => $settings
		]));
	}
}
