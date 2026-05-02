<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace NF\Widgets\Navigation\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Widget as Controller_Widget;

class Admin extends Controller_Widget
{
	public function index($settings = [])
	{
		return $this->view('admin', $settings);
	}

	public function vertical($settings = [])
	{
		return $this->view('admin', $settings);
	}
}


