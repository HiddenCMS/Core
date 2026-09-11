<?php

namespace HB\Widgets\Image\Controllers;

use HB\HiddenCMS\Loadables\Controller;

class Admin extends Controller
{
	public function index($settings = [])
	{
		$settings = array_merge(['image_id' => 0], $settings);

		return $this->view('admin', [
			'image_field' => $this->module('files')->picker_field('settings[image_id]', $settings['image_id'], 'Image', 'image', 'Aucune image sélectionnée'),
			'settings' => $settings
		]);
	}
}
