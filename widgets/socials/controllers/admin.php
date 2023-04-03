<?php
/**
 * https://neofr.ag
 * @author: Jérémy VALENTIN <jeremy.valentin@neofr.ag>
 */

namespace HD\Widgets\Socials\Controllers;

use HD\Hidden\Loadables\Controller;

class Admin extends Controller
{
	public function index($settings = [])
	{
		return $this->view('admin', array_merge([
			'display_panel'   => 'non',
			'social_display'  => 'col-12',
			'social_style'    => 'btn btn-social',
			'content_display' => 'all',
			'icon_size'       => 'fa-1x',
			'padding_top'     => 0,
			'padding_right'   => 0,
			'padding_bottom'  => 0,
			'padding_left'    => 0,
			'margin_top'      => 0,
			'margin_right'    => 0,
			'margin_bottom'   => 0,
			'margin_left'     => 0
		], $settings));
	}
}
