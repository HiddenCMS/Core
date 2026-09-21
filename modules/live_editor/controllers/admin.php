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
				->css('layout-builder')
				->css('file_picker')
				->css('jquery-ui.min')
				->js('jquery-ui.min')
				->js('tinymce/tinymce.min')
				->js('form_tinymce')
				->js('file_picker')
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
		$outline = NULL;
		$themes = $reserved_routes = $selected_reserved_routes = $pages = $selected_pages = [];
		$styles_row = $styles_widget = '';
		if ($outline_id !== NULL && $outlines_module && ($outline = $outlines_module->model2('outline')->get_outline_by_id($outline_id)))
		{
			$theme = $this->theme($outline['theme']);
			$styles_row = $theme->styles_row();
			$styles_widget = $theme->styles_widget();
			$outline_model = $outlines_module->model2('outline');
			$themes = $outline_model->get_themes();
			$reserved_routes = $outline_model->get_reserved_route_choices();
			$selected_reserved_routes = $outline_model->get_reserved_routes($outline_id);

			foreach ($this->db->select('p.page_id', 'p.name', 'p.outline_id', 'pl.title')->from('pages p')->join('pages_lang pl', 'p.page_id = pl.page_id')->where('pl.lang', $this->config->lang->info()->name)->order_by('pl.title ASC')->get(FALSE) as $page)
			{
				$pages[(int)$page['page_id']] = $page['title'].' (/'.$page['name'].')';
				if ((int)$page['outline_id'] === (int)$outline_id) $selected_pages[] = (int)$page['page_id'];
			}
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
			'outline'       => $outline ?: [],
			'themes'        => $themes,
			'reserved_routes' => $reserved_routes,
			'selected_reserved_routes' => $selected_reserved_routes,
			'pages'          => $pages,
			'selected_pages' => $selected_pages,
			'layout'        => $layout,
			'widgets'       => $widgets ?: [],
			'types'         => $types ?: [],
			'icons'         => $icons ?: [],
			'styles_row'    => $styles_row,
			'styles_widget' => $styles_widget
		]);
	}
}


