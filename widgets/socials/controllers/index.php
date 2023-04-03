<?php
/**
 * https://neofr.ag
 * @author: Jérémy VALENTIN <jeremy.valentin@neofr.ag>
 */

namespace HD\Widgets\Socials\Controllers;

use HD\Hidden\Loadables\Controllers\Widget as Controller_Widget;

class Index extends Controller_Widget
{
	public function index($settings = [])
	{
		$this->css('socials');

		if ($settings['display_panel'] == 'oui')
		{
			return $this->panel()
						->body($this->view('index', [
							'settings' => $settings
						]));
		}

		return $this->view('index', [
			'settings' => $settings
		]);
	}
}
