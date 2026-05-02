<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries;

use HB\HiddenCMS\Displayables\Col;
use HB\HiddenCMS\Displayables\Row;
use HB\HiddenCMS\Displayables\Widget;
use HB\HiddenCMS\Library;

class Disposition extends Library
{
	public function encode($disposition)
	{
		return json_encode($this->to_array($disposition), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	}

	public function decode($disposition)
	{
		if (!$disposition)
		{
			return $this->array();
		}

		$disposition = trim($disposition);

		$rows = json_decode($disposition, TRUE);

		if (!is_array($rows))
		{
			return $this->array();
		}

		return $this->from_array($rows);
	}

	public function to_array($disposition)
	{
		$rows = [];

		if (!is_array($disposition) && !is_a($disposition, 'Traversable'))
		{
			return $rows;
		}

		foreach ($disposition as $row)
		{
			if (!is_a($row, Row::class))
			{
				continue;
			}

			$cols = [];

			foreach ($row as $col)
			{
				if (!is_a($col, Col::class))
				{
					continue;
				}

				$widgets = [];

				foreach ($col as $widget)
				{
					if (is_a($widget, Widget::class))
					{
						$widgets[] = [
							'id'    => (int)$widget->widget_id(),
							'style' => $widget->style() ?: NULL,
							'size'  => $widget->size() ?: NULL
						];
					}
				}

				$cols[] = [
					'size'    => $col->size() ?: NULL,
					'widgets' => $widgets
				];
			}

			$rows[] = [
				'style' => $row->style() ?: NULL,
				'cols'  => $cols
			];
		}

		return $rows;
	}

	public function from_array($rows)
	{
		$disposition = $this->array();

		foreach ($rows as $row_data)
		{
			$row = $this->row();

			if (!empty($row_data['style']))
			{
				$row->style($row_data['style']);
			}

			foreach (!empty($row_data['cols']) && is_array($row_data['cols']) ? $row_data['cols'] : [] as $col_data)
			{
				$col = $this->col();

				if (!empty($col_data['size']))
				{
					$col->size($col_data['size']);
				}

				foreach (!empty($col_data['widgets']) && is_array($col_data['widgets']) ? $col_data['widgets'] : [] as $widget_data)
				{
					if (!empty($widget_data['id']))
					{
						$widget = $this->widget((int)$widget_data['id']);

						if (!empty($widget_data['style']))
						{
							$widget->style($widget_data['style']);
						}

						if (!empty($widget_data['size']))
						{
							$widget->size($widget_data['size']);
						}

						$col->append($widget);
					}
				}

				$row->append($col);
			}

			$disposition->append($row);
		}

		return $disposition;
	}
}
