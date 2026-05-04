<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Live_Editor\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Admin extends Controller_Module
{
	public function index()
	{
		$this	->css('fonts/open-sans')
				->css('live-editor')
				->js('live-editor')
				->css('jquery-ui.min')
				->js('jquery-ui.min');

		$outlines = [];
		$outline_id = !empty($_GET['outline_id']) ? (int)$_GET['outline_id'] : 0;
		$outline_title = '';

		$theme = $this->theme($this->config->default_theme);

		if (($layouts = @HB()->module('layouts')) && $layouts->is_enabled())
		{
			foreach ($layouts->model()->get_outlines(TRUE) as $outline)
			{
				$outlines[$outline['outline_id']] = $outline['title'];
			}

			if (!$outline_id && $outlines)
			{
				$outline_id = key($outlines);
			}

			if ($outline_id && isset($outlines[$outline_id]))
			{
				$outline_title = $outlines[$outline_id];
			}
		}

		return $this->view('index', [
			'outlines'      => $outlines,
			'outline_id'    => $outline_id,
			'outline_title' => $outline_title,
			'styles_row'    => $theme->styles_row(),
			'styles_widget' => $theme->styles_widget()
		]);
	}
}


