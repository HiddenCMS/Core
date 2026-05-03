<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Layouts\Models;

use HB\HiddenCMS\Loadables\Model;

class Layouts extends Model
{
	public function get_outlines()
	{
		return $this->db	->select('*')
						->from('layouts_outlines')
						->where('enabled', TRUE)
						->order_by('title ASC')
						->get();
	}

	public function get_outline_choices()
	{
		$outlines = [];

		foreach ($this->get_outlines() as $outline)
		{
			$outlines[$outline['outline_id']] = $outline['title'];
		}

		return $outlines;
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
}
