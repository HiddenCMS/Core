<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Live_Editor\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module_Checker;

class Admin_Ajax_Checker extends Module_Checker
{
	public function layout_save()
	{
		if (!$this->user->admin)
		{
			return;
		}

		$outline_id = (int)post('outline_id');
		$layout = json_decode((string)post('layout'), TRUE);

		if ($outline_id > 0 && is_array($layout) && $this->module('outlines')->model2('outline')->get_outline_by_id($outline_id))
		{
			return [$outline_id, $layout];
		}
	}

	public function builder_widget_admin()
	{
		if (!$this->user->admin)
		{
			return;
		}

		$widget_name = trim((string)post('widget'));
		$type = trim((string)post('type')) ?: 'index';
		$settings = json_decode((string)post('settings'), TRUE);
		$settings = is_array($settings) ? $settings : [];
		$this->model()->get_widgets($widgets, $types);

		if (isset($widgets[$widget_name]) && (isset($types[$widget_name][$type]) || $type === 'index'))
		{
			return [$widget_name, $type, $settings];
		}
	}

	public function assignments_save()
	{
		if (!$this->user->admin) return;
		$outline_id = (int)post('outline_id');
		if ($outline_id > 0 && $this->module('outlines')->model2('outline')->get_outline_by_id($outline_id))
		{
			return [$outline_id, array_map('intval', (array)post('pages')), array_map('strval', (array)post('routes'))];
		}
	}

	public function options_save()
	{
		if (!$this->user->admin) return;
		$outline_id = (int)post('outline_id');
		$title = trim((string)post('title'));
		$name = url_title(trim((string)post('name')) ?: $title);
		$theme = trim((string)post('theme'));
		$model = $this->module('outlines')->model2('outline');
		if ($outline_id > 0 && $title !== '' && $name !== '' && $model->get_outline_by_id($outline_id) && isset($model->get_themes()[$theme]) && !$model->name_exists($name, $outline_id))
		{
			return [$outline_id, $name, $title, $theme, (bool)post('base'), (bool)post('breadcrumb'), (bool)post('enabled')];
		}
	}

	public function zone_fork()
	{
		return $this->_check_disposition('disposition_id', 'url');
	}

	public function row_add()
	{
		return $this->_check_disposition('disposition_id');
	}

	public function row_move()
	{
		return $this->_check_disposition('disposition_id', 'row_id', 'position');
	}

	public function row_style()
	{
		return $this->_check_disposition('disposition_id', 'row_id', 'style');
	}

	public function row_delete()
	{
		return $this->_check_disposition('disposition_id', 'row_id');
	}

	public function col_add()
	{
		return $this->_check_disposition('disposition_id', 'row_id');
	}

	public function col_move()
	{
		return $this->_check_disposition('disposition_id', 'row_id', 'col_id', 'position');
	}

	public function col_size()
	{
		return $this->_check_disposition('disposition_id', 'row_id', 'col_id', 'size');
	}

	public function col_delete()
	{
		return $this->_check_disposition('disposition_id', 'row_id', 'col_id');
	}

	public function widget_add()
	{
		if ($args = list(,,,,, $widget_name, $type) = $this->_check_disposition('disposition_id', 'row_id', 'col_id', 'title', 'widget', 'type', 'settings'))
		{
			$this->model()->get_widgets($widgets, $types);

			if (isset($widgets[$widget_name]) && (isset($types[$widget_name][$type]) || $type == 'index'))
			{
				return $args;
			}
		}
	}

	public function widget_move()
	{
		return $this->_check_disposition('disposition_id', 'row_id', 'col_id', 'widget_id', 'position');
	}

	public function widget_style()
	{
		return $this->_check_disposition('disposition_id', 'row_id', 'col_id', 'widget_id', 'style');
	}

	public function widget_admin()
	{
		if ($this->user->admin)
		{
			$post = post();

			if (!empty($post['widget_id']) && $widget = $this->db	->select('widget', 'type', 'settings')
																	->from('widgets')
																	->where('widget_id', $post['widget_id'])
																	->row())
			{
				return [$widget['widget'], $widget['type'], $this->storage->decode($widget['settings'], NULL)];
			}
			else if (!empty($post['widget']) && isset($post['type']))
			{
				return [$post['widget'], $post['type'] ?: 'index'];
			}
		}
	}

	public function widget_settings()
	{
		if ((list($disposition_id, $disposition, $row_id, $col_id, $widget_id) = $this->_check_disposition('disposition_id', 'row_id', 'col_id', 'widget_id')))
		{
			if ($widget_id == -1)
			{
				return [];
			}
			else if ($widget = $this->model()->check_widget($disposition->get($row_id, $col_id, $widget_id)->widget_id()))
			{
				return $widget;
			}
		}
	}

	public function widget_update()
	{
		if ((list($disposition_id, $disposition, $row_id, $col_id, $widget_id, $title, $widget_name, $type, $settings) = $this->_check_disposition('disposition_id', 'row_id', 'col_id', 'widget_id', 'title', 'widget', 'type', 'settings')) &&
			($widget = $this->model()->check_widget($disposition->get($row_id, $col_id, $widget_id)->widget_id())))
		{
			$this->model()->get_widgets($widgets, $types);

			if (isset($widgets[$widget_name]) && (isset($types[$widget_name][$type]) || $type == 'index'))
			{
				$widget['title']    = $title;
				$widget['widget']   = $widget_name;
				$widget['type']     = $type;
				$widget['settings'] = $settings;

				return [$disposition_id, $disposition, $row_id, $col_id, $widget_id, $widget['widget_id'], $widget['widget'], $widget['type'], $widget['title'], $widget['settings']];
			}
		}
	}

	public function widget_delete()
	{
		return $this->_check_disposition('disposition_id', 'row_id', 'col_id', 'widget_id');
	}

	private function _check_disposition()
	{
		if ($this->user->admin && $check = post_check(func_get_args()))
		{
			array_splice($check, 1, 0, [$this->model()->get_disposition($check['disposition_id'], $theme, $page, $zone)]);

			$check[] = $theme;
			$check[] = $page;
			$check[] = $zone;

			return array_values($check);
		}
	}
}
