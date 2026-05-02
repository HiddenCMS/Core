<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Widgets\Module\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Widget as Controller_Widget;

class Index extends Controller_Widget
{
	public function index($config = [])
	{
		$this->title($this->output->data->get('module', 'title'));
		return $this->output->module();
	}
}


