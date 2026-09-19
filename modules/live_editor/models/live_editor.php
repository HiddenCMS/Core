<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Live_Editor\Models;

use HB\HiddenCMS\Loadables\Model;

class Live_Editor extends Model
{
	public function get_layout($outline_id)
	{
		$outline = HB()->module('outlines')->model2('outline')->get_outline_by_id((int)$outline_id);

		if (!$outline)
		{
			return [];
		}

		$theme = $this->theme($outline['theme']);
		$dispositions = [];
		foreach ($this->db->from('dispositions')->where('theme', $outline['theme'])->where('page', 'outline:'.(int)$outline_id)->get() as $record)
		{
			$dispositions[(int)$record['zone']] = $record;
		}

		$layout = [];
		foreach ($theme->info()->zones as $zone => $identifier)
		{
			$record = isset($dispositions[$zone]) ? $dispositions[$zone] : NULL;
			$rows = $record ? $this->disposition->to_array($this->disposition->decode($record['disposition'])) : [];

			foreach ($rows as &$row)
			{
				foreach ($row['cols'] as &$col)
				{
					foreach ($col['widgets'] as &$widget)
					{
						$data = $this->db->from('widgets')->where('widget_id', $widget['id'])->row();
						$widget = array_merge($widget, [
							'widget'   => !empty($data['widget']) ? $data['widget'] : '',
							'type'     => !empty($data['type']) ? $data['type'] : 'index',
							'title'    => !empty($data['title']) ? html_entity_decode($data['title'], ENT_QUOTES, 'UTF-8') : '',
							'settings' => !empty($data['settings']) ? $this->storage->decode($data['settings']) : []
						]);
					}
				}
			}

			$layout[] = [
				'disposition_id' => $record ? (int)$record['disposition_id'] : 0,
				'zone'           => (int)$zone,
				'title'          => (string)$theme->zone_title($zone),
				'rows'           => $rows
			];
		}

		return $layout;
	}

	public function save_layout($outline_id, array $layout)
	{
		$outline = HB()->module('outlines')->model2('outline')->get_outline_by_id((int)$outline_id);

		if (!$outline)
		{
			throw new \InvalidArgumentException((string)$this->lang('Unknown outline'));
		}

		$records = [];
		$old_widgets = [];
		foreach ($this->db->from('dispositions')->where('theme', $outline['theme'])->where('page', 'outline:'.(int)$outline_id)->get() as $record)
		{
			$records[(int)$record['zone']] = $record;
			$this->collect_widget_ids($this->disposition->decode($record['disposition']), $old_widgets);
		}

		$this->db->begin_transaction();
		try
		{
			$used_widgets = [];
			$updates = [];
			$processed_zones = [];
			foreach ($layout as $zone_data)
			{
				$zone = isset($zone_data['zone']) ? (int)$zone_data['zone'] : -1;
				if (!isset($records[$zone]) || !isset($zone_data['rows']) || !is_array($zone_data['rows'])) continue;
				if (isset($processed_zones[$zone])) throw new \InvalidArgumentException((string)$this->lang('Invalid layout'));
				$processed_zones[$zone] = TRUE;

				$disposition = $this->array();
				foreach ($zone_data['rows'] as $row_data)
				{
					$row = $this->row();
					if (!empty($row_data['style']) && is_string($row_data['style'])) $row->style($this->normalize_style($row_data['style']));

					foreach (!empty($row_data['cols']) && is_array($row_data['cols']) ? $row_data['cols'] : [] as $col_data)
					{
						$col = $this->col()->size($this->normalize_col_size(isset($col_data['size']) ? $col_data['size'] : 'col-12'));

						foreach (!empty($col_data['widgets']) && is_array($col_data['widgets']) ? $col_data['widgets'] : [] as $widget_data)
						{
							$id = isset($widget_data['id']) ? (int)$widget_data['id'] : 0;
							$is_existing = $id && in_array($id, $old_widgets, TRUE);
							if (($id && !$is_existing) || ($id && in_array($id, $used_widgets, TRUE))) continue;

							if (!empty($widget_data['dirty']) || !$is_existing)
							{
								$definition = $this->normalize_widget($widget_data);
								if (!$definition) throw new \InvalidArgumentException((string)$this->lang('Invalid widget'));

								if ($is_existing)
								{
									$this->db->where('widget_id', $id)->update('widgets', $definition);
								}
								else
								{
									$id = (int)$this->db->insert('widgets', $definition);
									if (!$id) throw new \RuntimeException((string)$this->lang('Unable to create the widget'));
								}
							}

							$widget = $this->widget($id);
							if (!empty($widget_data['style']) && is_string($widget_data['style'])) $widget->style($this->normalize_style($widget_data['style']));
							if (!empty($widget_data['size']) && is_string($widget_data['size'])) $widget->size($widget_data['size']);
							$col->append($widget);
							$used_widgets[] = $id;
						}

						$row->append($col);
					}
					$disposition->append($row);
				}
				$updates[$records[$zone]['disposition_id']] = $disposition;
			}
			if (array_diff_key($records, $processed_zones)) throw new \InvalidArgumentException((string)$this->lang('Incomplete layout'));

			foreach ($updates as $disposition_id => $disposition)
			{
				$this->set_disposition($disposition_id, $disposition);
			}

			$deleted_widgets = array_values(array_diff($old_widgets, $used_widgets));
			if ($deleted_widgets)
			{
				$this->db->where('widget_id', $deleted_widgets)->delete('widgets');
			}

			$this->db->commit();
		}
		catch (\Throwable $error)
		{
			$this->db->rollback();
			throw $error;
		}

		return $this;
	}

	private function normalize_widget(array $widget_data)
	{
		$name = isset($widget_data['widget']) ? trim((string)$widget_data['widget']) : '';
		$type = !empty($widget_data['type']) ? trim((string)$widget_data['type']) : 'index';
		$this->get_widgets($widgets, $types);

		if (!isset($widgets[$name]) || (!isset($types[$name][$type]) && $type !== 'index'))
		{
			return FALSE;
		}

		$settings = [];
		if (array_key_exists('settings_form', $widget_data) && is_string($widget_data['settings_form']))
		{
			parse_str($widget_data['settings_form'], $settings);
		}
		else if (!empty($widget_data['settings']) && is_array($widget_data['settings']))
		{
			$settings = $widget_data['settings'];
		}

		return [
			'title'    => !empty($widget_data['title']) ? utf8_htmlentities(trim((string)$widget_data['title'])) : NULL,
			'widget'   => $name,
			'type'     => $type,
			'settings' => $this->widget($name)->get_settings($type, $settings)
		];
	}

	private function collect_widget_ids($disposition, array &$ids)
	{
		foreach ($disposition as $item)
		{
			if (is_a($item, 'HB\\HiddenCMS\\Displayables\\Widget'))
			{
				$ids[] = (int)$item->widget_id();
			}
			else if ($item && (is_array($item) || $item instanceof \Traversable))
			{
				$this->collect_widget_ids($item, $ids);
			}
		}
	}

	private function normalize_col_size($size)
	{
		$tokens = preg_split('/\s+/', trim((string)$size));
		$tokens = array_values(array_filter($tokens, function($token){
			return preg_match('/^col(?:-(?:sm|md|lg|xl))?-(?:[1-9]|1[0-2])$/', $token) || $token === 'col';
		}));

		return $tokens ? implode(' ', $tokens) : 'col-12';
	}

	private function normalize_style($style)
	{
		$tokens = preg_split('/\s+/', trim((string)$style));
		$tokens = array_values(array_unique(array_filter($tokens, function($token){
			return preg_match('/^[a-zA-Z0-9_-]+$/', $token);
		})));

		return implode(' ', $tokens);
	}

	public function get_disposition($disposition_id, &$theme, &$page, &$zone)
	{
		$disposition = $this->db	->select('disposition', 'theme', 'page', 'zone')
									->from('dispositions')
									->where('disposition_id', $disposition_id)
									->row();

		$theme = $disposition['theme'];
		$page  = $disposition['page'];
		$zone  = $disposition['zone'];

		return $this->disposition->decode($disposition['disposition']);
	}

	public function set_disposition($disposition_id, $disposition)
	{
		$this->db	->where('disposition_id', $disposition_id)
					->update('dispositions', [
						'disposition' => $this->disposition->encode($disposition)
					]);
	}

	public function delete_widgets($disposition)
	{
		$widgets = [];

		$disposition->each($f = function($a) use (&$f, &$widgets){
			if (is_a($a, 'HB\HiddenCMS\Displayables\Widget'))
			{
				$widgets[] = $a->widget_id();
			}
			else if ($a)
			{
				$a->each($f);
			}
		});

		if ($widgets)
		{
			$this->db	->where('widget_id', $widgets)
						->delete('widgets');
		}
	}

	public function check_widget($widget_id)
	{
		$widget = $this->db	->from('widgets')
							->where('widget_id', $widget_id)
							->row();

		if ($widget)
		{
			if ($widget['settings'] !== NULL)
			{
				$widget['settings'] = $this->storage->encode($widget['settings']);
			}

			return $widget;
		}
		else
		{
			return FALSE;
		}
	}

	public function get_widgets(&$widgets, &$types, &$icons = [])
	{
		foreach (HB()->model2('addon')->get('widget') as $widget)
		{
			$info = $widget->info();
			$widgets[$name = $info->name] = $info->title;
			$icons[$name] = !empty($info->icon) ? $info->icon : 'fas fa-puzzle-piece';

			if (!empty($info->types))
			{
				$types[$name] = $info->types;
				array_natsort($types[$name]);
			}
		}

		array_natsort($widgets);
		$icons = array_replace(array_fill_keys(array_keys($widgets), 'fas fa-puzzle-piece'), $icons);
	}
}


