<?php

namespace HB\Widgets\Hero\Controllers;

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
			'image_id'   => $image_id,
			'eyebrow'    => $this->text($settings, 'eyebrow'),
			'heading'    => $this->text($settings, 'heading'),
			'content'    => $this->text($settings, 'content'),
			'button'     => $this->text($settings, 'button'),
			'link'       => $this->text($settings, 'link'),
			'align'      => $this->choice($settings, 'align', ['left', 'center', 'right'], 'left'),
			'height'     => $this->choice($settings, 'height', ['compact', 'medium', 'large'], 'medium'),
			'overlay'    => $this->choice($settings, 'overlay', ['light', 'medium', 'dark'], 'medium')
		];
	}

	private function text($settings, $name)
	{
		return utf8_htmlentities(trim(isset($settings[$name]) ? $settings[$name] : ''));
	}

	private function choice($settings, $name, array $choices, $default)
	{
		return in_array(isset($settings[$name]) ? $settings[$name] : '', $choices, TRUE) ? $settings[$name] : $default;
	}
}
