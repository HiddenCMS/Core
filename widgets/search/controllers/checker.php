<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Widgets\Search\Controllers;

use HB\HiddenCMS\Loadables\Controller;

class Checker extends Controller
{
	public function index($settings = [])
	{
		return [
			'align' => !empty($settings['align']) && in_array($settings['align'], ['float-left', 'float-right']) ? $settings['align'] : 'float-right'
		];
	}
}


