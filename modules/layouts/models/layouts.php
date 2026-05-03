<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Layouts\Models;

use HB\HiddenCMS\Loadables\Model;

class Layouts extends Model
{
	public function get_outlines($enabled = NULL)
	{
		$this->db	->select('*')
					->from('layouts_outlines')
					->order_by('title ASC');

		if ($enabled !== NULL)
		{
			$this->db->where('enabled', $enabled);
		}

		return $this->db->get();
	}

	public function get_outline_choices()
	{
		$outlines = [];

		foreach ($this->get_outlines(TRUE) as $outline)
		{
			$outlines[$outline['outline_id']] = $outline['title'];
		}

		return $outlines;
	}

	public function get_regions($theme = NULL)
	{
		return HiddenCMS()->theme($theme ?: $this->config->default_theme)->regions();
	}

	public function get_themes()
	{
		$themes = [];

		foreach (HiddenCMS()->model2('addon')->get('theme') as $theme)
		{
			if ($theme->info()->name != 'admin')
			{
				$themes[$theme->info()->name] = (string)$theme->info()->title;
			}
		}

		return $themes;
	}

	public function get_widgets()
	{
		$widgets = [];

		foreach ($this->db->select('widget_id', 'widget', 'type', 'title')->from('widgets')->order_by('widget ASC', 'type ASC')->get() as $widget)
		{
			$label = ($widget['title'] ?: $widget['widget'].' / '.$widget['type']);
			$widgets[$widget['widget_id']] = $label;
		}

		return $widgets;
	}

	public function get_modules()
	{
		$modules = [];

		foreach (HiddenCMS()->model2('addon')->get('module') as $module)
		{
			if ($module->is_enabled() && $module->is_front())
			{
				$modules[$module->info()->name] = (string)$module->info()->title;
			}
		}

		uasort($modules, 'strnatcasecmp');

		return $modules;
	}

	public function default_layout($theme = NULL)
	{
		$layout = [];

		foreach ($this->get_regions($theme) as $region => $title)
		{
			$layout[$region] = [];
		}

		if (isset($layout['content']))
		{
			$layout['content'][] = [
				'columns' => [
					[
						'size'  => 'col-12',
						'items' => [
							['type' => 'page_content']
						]
					]
				]
			];
		}

		return $layout;
	}

	public function get_outline($outline_id = NULL)
	{
		$this->db	->select('*')
					->from('layouts_outlines')
					->where('enabled', TRUE);

		if ($outline_id)
		{
			$this->db->where('outline_id', $outline_id);
		}
		else
		{
			$this->db->where('base', TRUE);
		}

		return $this->db->row();
	}

	public function check_outline($outline_id, $title, $all = FALSE)
	{
		$this->db	->select('*')
					->from('layouts_outlines')
					->where('outline_id', $outline_id);

		if (!$all)
		{
			$this->db->where('enabled', TRUE);
		}

		$outline = $this->db->row();

		return $outline && url_title($outline['title']) == $title ? $outline : FALSE;
	}

	public function add_outline($name, $title, $theme, $layout, $base, $enabled)
	{
		$name = $name ?: url_title($title);

		$outline_id = $this->db->insert('layouts_outlines', [
			'name'     => $name,
			'title'    => $title,
			'theme'    => $theme ?: $this->config->default_theme,
			'layout'   => $this->storage->encode($this->normalize_layout($layout)),
			'settings' => $this->storage->encode([]),
			'base'     => $base,
			'enabled'  => $enabled
		]);

		if (!$outline_id)
		{
			return FALSE;
		}

		if ($base)
		{
			$this->db	->where('outline_id <>', $outline_id)
						->update('layouts_outlines', ['base' => FALSE]);
		}

		return $outline_id;
	}

	public function edit_outline($outline_id, $name, $title, $theme, $layout, $base, $enabled)
	{
		$name = $name ?: url_title($title);

		$updated = $this->db	->where('outline_id', $outline_id)
							->update('layouts_outlines', [
						'name'    => $name,
						'title'   => $title,
						'theme'   => $theme ?: $this->config->default_theme,
						'layout'  => $this->storage->encode($this->normalize_layout($layout)),
						'base'    => $base,
						'enabled' => $enabled
					]);

		if ($updated === NULL)
		{
			return FALSE;
		}

		if ($base)
		{
			$this->db	->where('outline_id <>', $outline_id)
						->update('layouts_outlines', ['base' => FALSE]);
		}

		return $this;
	}

	public function render_region($outline_id, $region)
	{
		if (!($outline = $this->get_outline($outline_id)) && !($outline = $this->get_outline()))
		{
			return '';
		}

		$layout = $this->storage->decode($outline['layout'], []);

		if (!array_key_exists($region, $layout) && !$outline['base'] && ($base = $this->get_outline()))
		{
			$layout = $this->storage->decode($base['layout'], []);
		}

		$rows = isset($layout[$region]) && is_array($layout[$region]) ? $layout[$region] : [];
		$output = $this->array();

		foreach ($rows as $row)
		{
			if ($rendered = $this->render_row($row))
			{
				$output->append($rendered);
			}
		}

		return $output;
	}

	private function render_row($row_data)
	{
		$row = $this->row();

		if (!empty($row_data['style']))
		{
			$row->style($row_data['style']);
		}

		foreach (!empty($row_data['columns']) && is_array($row_data['columns']) ? $row_data['columns'] : [] as $column_data)
		{
			$column = $this->col();

			if (!empty($column_data['size']))
			{
				$column->size($column_data['size']);
			}

			foreach (!empty($column_data['items']) && is_array($column_data['items']) ? $column_data['items'] : [] as $item)
			{
				if (($rendered = $this->render_item($item)) !== '')
				{
					$column->append($rendered);
				}
			}

			$row->append($column);
		}

		return $row;
	}

	private function render_item($item)
	{
		if (empty($item['type']))
		{
			return '';
		}

		if ($item['type'] == 'page_content')
		{
			return $this->output->data->get('module', 'content') ?: '';
		}

		if ($item['type'] == 'widget' && !empty($item['widget_id']))
		{
			$widget = $this->widget((int)$item['widget_id']);

			if (!empty($item['style']))
			{
				$widget->style($item['style']);
			}

			if (!empty($item['size']))
			{
				$widget->size($item['size']);
			}

			return $widget;
		}

		if ($item['type'] == 'module' && !empty($item['module']))
		{
			return $this->output->module_content($item['module'], strtoarray('/', isset($item['route']) ? $item['route'] : ''), FALSE);
		}

		if ($item['type'] == 'static')
		{
			return bbcode(isset($item['content']) ? $item['content'] : '');
		}

		return '';
	}

	private function normalize_layout($layout)
	{
		$output = [];

		foreach (is_array($layout) ? $layout : [] as $region => $rows)
		{
			$output[$region] = [];

			foreach (is_array($rows) ? $rows : [] as $row)
			{
				$columns = [];

				foreach (!empty($row['columns']) && is_array($row['columns']) ? $row['columns'] : [] as $column)
				{
					$items = [];

					foreach (!empty($column['items']) && is_array($column['items']) ? $column['items'] : [] as $item)
					{
						if (!empty($item['type']))
						{
							$items[] = array_filter([
								'type'      => $item['type'],
								'widget_id' => !empty($item['widget_id']) ? (int)$item['widget_id'] : NULL,
								'module'    => !empty($item['module']) ? $item['module'] : NULL,
								'route'     => !empty($item['route']) ? trim($item['route'], '/') : NULL,
								'content'   => !empty($item['content']) ? $item['content'] : NULL,
								'style'     => !empty($item['style']) ? $item['style'] : NULL,
								'size'      => !empty($item['size']) ? $item['size'] : NULL
							], function($value){
								return $value !== NULL;
							});
						}
					}

					$columns[] = [
						'size'  => !empty($column['size']) ? $column['size'] : 'col-12',
						'items' => $items
					];
				}

				if ($columns)
				{
					$output[$region][] = array_filter([
						'style'   => !empty($row['style']) ? $row['style'] : NULL,
						'columns' => $columns
					], function($value){
						return $value !== NULL;
					});
				}
			}
		}

		return $output;
	}
}
