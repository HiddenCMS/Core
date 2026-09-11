<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Widgets\Html\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Widget as Controller_Widget;

class Index extends Controller_Widget
{
	public function index($settings = [])
	{
		$content = isset($settings['content']) ? $settings['content'] : '';

		if (preg_match('/\[(b|i|u|s|url|img|list|quote|code)(=|\])/i', $content))
		{
			$content = bbcode($content);
		}

		return $this->panel()->body($content);
	}

	public function html($settings = [])
	{
		return $this->panel()->body($settings['content']);
	}
}


