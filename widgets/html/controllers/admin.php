<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Widgets\Html\Controllers;

use HD\Hidden\Loadables\Controller;

class Admin extends Controller
{
	public function index($settings = [])
	{
		return $this->view('bbcode', $settings);
	}

	public function html($settings = [])
	{
		return '<textarea class="form-control" name="settings[content]" placeholder="'.$this->lang('Code HTML').'" rows="6">'.(isset($settings['content']) ? $settings['content'] : '').'</textarea>';
	}
}
