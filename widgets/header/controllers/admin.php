<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace NF\Widgets\Header\Controllers;

use HB\HiddenCMS\Loadables\Controller;

class Admin extends Controller
{
	public function index($settings = [])
	{
		return $this->view('admin', $settings);
	}
}


