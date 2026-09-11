<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Widgets\Html\Controllers;

use HB\HiddenCMS\Loadables\Controller;

class Checker extends Controller
{
	public function index($settings = [])
	{
		return [
			'content' => isset($settings['content']) ? trim($settings['content']) : ''
		];
	}

	public function html($settings = [])
	{
		return [
			'content' => isset($settings['content']) ? $settings['content'] : ''
		];
	}
}


