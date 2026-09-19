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
		if (isset($_GET['legacy']))
		{
			return $this->legacy();
		}

		$this	->css('fonts/open-sans')
				->css('live-editor')
				->css('layout-builder')
				->css('jquery-ui.min')
				->js('jquery-ui.min')
				->js('layout-builder');

		$outlines = [];
		$outlines_module = NULL;
		$outline_id = isset($_GET['outline_id']) ? (int)$_GET['outline_id'] : NULL;
		$outline_title = '';

		if (($outlines_module = @HB()->module('outlines')) && $outlines_module->is_enabled())
		{
			foreach ($outlines_module->model2('outline')->get_outlines(TRUE) as $outline)
			{
				$outlines[$outline['outline_id']] = $outline['title'];
			}

			if ($outline_id === NULL && $outlines)
			{
				$outline_id = key($outlines);
			}

			if ($outline_id !== NULL && array_key_exists($outline_id, $outlines))
			{
				$outline_title = $outlines[$outline_id];
			}
		}

		$layout = $outline_id !== NULL ? $this->model()->get_layout($outline_id) : [];
		$styles_row = $styles_widget = '';
		if ($outline_id !== NULL && $outlines_module && ($outline = $outlines_module->model2('outline')->get_outline_by_id($outline_id)))
		{
			$theme = $this->theme($outline['theme']);
			$styles_row = $theme->styles_row();
			$styles_widget = $theme->styles_widget();
		}
		$widgets = $types = $icons = [];
		$this->model()->get_widgets($widgets, $types, $icons);
		foreach ($widgets as &$widget_title) $widget_title = (string)$widget_title;
		foreach ($types as &$widget_types)
		{
			foreach ($widget_types as &$type_title) $type_title = (string)$type_title;
		}

		return $this->view('builder', [
			'outlines'      => $outlines,
			'outline_id'    => $outline_id,
			'outline_title' => $outline_title,
			'layout'        => $layout,
			'widgets'       => $widgets ?: [],
			'types'         => $types ?: [],
			'icons'         => $icons ?: [],
			'styles_row'    => $styles_row,
			'styles_widget' => $styles_widget
		]);
	}

	private function legacy()
	{
		$this	->css('fonts/open-sans')
				->css('live-editor')
				->css('jquery-ui.min')
				->js('jquery-ui.min')
				->js('live-editor');

		$outlines = [];
		$outline_id = isset($_GET['outline_id']) ? (int)$_GET['outline_id'] : NULL;
		$outline_title = '';
		$theme = $this->theme($this->config->default_theme);

		if (($outlines_module = @HB()->module('outlines')) && $outlines_module->is_enabled())
		{
			foreach ($outlines_module->model2('outline')->get_outlines(TRUE) as $outline)
			{
				$outlines[$outline['outline_id']] = $outline['title'];
			}
			if ($outline_id === NULL && $outlines) $outline_id = key($outlines);
			if ($outline_id !== NULL && isset($outlines[$outline_id])) $outline_title = $outlines[$outline_id];
		}

		return $this->view('index', [
			'outlines' => $outlines,
			'outline_id' => $outline_id,
			'outline_title' => $outline_title,
			'styles_row' => $theme->styles_row(),
			'styles_widget' => $theme->styles_widget()
		]);
	}
}


