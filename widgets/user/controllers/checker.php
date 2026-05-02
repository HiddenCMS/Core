<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace NF\Widgets\User\Controllers;

use HB\HiddenCMS\Loadables\Controller;

class Checker extends Controller
{
	public function index_mini($settings = [])
	{
		return [
			'align' => !empty($settings['align']) && in_array($settings['align'], ['justify-content-start', 'justify-content-end']) ? $settings['align'] : 'justify-content-end'
		];
	}
}


