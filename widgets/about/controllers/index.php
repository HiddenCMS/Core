<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Widgets\About\Controllers;

use HD\Hidden\Loadables\Controllers\Widget as Controller_Widget;

class Index extends Controller_Widget
{
	public function index($settings = [])
	{
		if ($settings['display_panel'] == 'oui')
		{
			return $this->panel()
						->body($this->view('about', [
							'settings' => $settings
						]));
		}

		return $this->view('about', [
			'settings' => $settings
		]);
	}
}
