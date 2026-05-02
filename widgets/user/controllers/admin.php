<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Widgets\User\Controllers;

use HB\HiddenCMS\Loadables\Controller;

class Admin extends Controller
{
	public function index_mini($settings = [])
	{
		return $this->view('admin_mini', $settings);
	}
}


