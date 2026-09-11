<?php

namespace HB\Widgets\Image\Controllers;

use HB\HiddenCMS\Loadables\Controller;

class Checker extends Controller
{
	public function index($settings = [])
	{
		$image_id = isset($settings['image_id']) ? (int)$settings['image_id'] : 0;

		if (!$image_id || $this->db->from('file')->where('id', $image_id)->empty())
		{
			$image_id = 0;
		}

		return [
			'image_id' => $image_id,
			'alt'      => utf8_htmlentities(trim(isset($settings['alt']) ? $settings['alt'] : '')),
			'caption'  => utf8_htmlentities(trim(isset($settings['caption']) ? $settings['caption'] : '')),
			'link'     => utf8_htmlentities(trim(isset($settings['link']) ? $settings['link'] : '')),
			'ratio'    => in_array(isset($settings['ratio']) ? $settings['ratio'] : '', ['auto', 'landscape', 'square', 'portrait'], TRUE) ? $settings['ratio'] : 'auto'
		];
	}
}
