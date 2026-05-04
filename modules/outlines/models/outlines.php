<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Outlines\Models;

use HB\HiddenCMS\Loadables\Model;

class Outlines extends Model
{
	public function get_outlines($enabled = NULL)
	{
		$this->db	->select('*')
					->from('outlines')
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

	public function get_outline($outline_id = NULL)
	{
		$this->db	->select('*')
					->from('outlines')
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

	public function get_outline_by_id($outline_id)
	{
		return $this->db	->select('*')
						->from('outlines')
						->where('outline_id', $outline_id)
						->row(FALSE);
	}

	public function check_outline($outline_id, $title, $all = FALSE)
	{
		$this->db	->select('*')
					->from('outlines')
					->where('outline_id', $outline_id);

		if (!$all)
		{
			$this->db->where('enabled', TRUE);
		}

		$outline = $this->db->row();

		return $outline && url_title($outline['title']) == $title ? $outline : FALSE;
	}

	public function add_outline($name, $title, $theme, $base, $enabled)
	{
		$name = $name ?: url_title($title);
		$theme = $theme ?: $this->config->default_theme;

		$outline_id = $this->db->insert('outlines', [
			'name'    => $name,
			'title'   => $title,
			'theme'   => $theme,
			'base'    => $base,
			'enabled' => $enabled
		]);

		if (!$outline_id)
		{
			return FALSE;
		}

		$this->create_dispositions($outline_id, $theme);

		if ($base)
		{
			$this->db	->where('outline_id <>', $outline_id)
						->update('outlines', ['base' => FALSE]);
		}

		return $outline_id;
	}

	public function edit_outline($outline_id, $name, $title, $theme, $base, $enabled)
	{
		$name = $name ?: url_title($title);
		$theme = $theme ?: $this->config->default_theme;

		$updated = $this->db	->where('outline_id', $outline_id)
								->update('outlines', [
									'name'    => $name,
									'title'   => $title,
									'theme'   => $theme,
									'base'    => $base,
									'enabled' => $enabled
								]);

		if ($updated === NULL)
		{
			return FALSE;
		}

		$this->create_dispositions($outline_id, $theme);

		if ($base)
		{
			$this->db	->where('outline_id <>', $outline_id)
						->update('outlines', ['base' => FALSE]);
		}

		return $this;
	}

	public function duplicate_outline($outline_id, $title)
	{
		if (!($outline = $this->get_outline_by_id($outline_id)))
		{
			return FALSE;
		}

		$new_outline_id = $this->db->insert('outlines', [
			'name'    => url_title($title),
			'title'   => $title,
			'theme'   => $outline['theme'],
			'base'    => FALSE,
			'enabled' => TRUE
		]);

		if (!$new_outline_id)
		{
			return FALSE;
		}

		foreach ($this->get_outline_dispositions($outline) as $disposition)
		{
			$output = $this->disposition->decode($disposition['disposition']);
			$this->duplicate_disposition_widgets($output);

			$this->db->insert('dispositions', [
				'theme'       => $outline['theme'],
				'page'        => $this->outline_page($new_outline_id),
				'zone'        => $disposition['zone'],
				'disposition' => $this->disposition->encode($output)
			]);
		}

		return $new_outline_id;
	}

	public function delete_outline($outline_id)
	{
		if (!($outline = $this->get_outline_by_id($outline_id)) || $outline['base'])
		{
			return FALSE;
		}

		if (!($base = $this->get_outline()))
		{
			return FALSE;
		}

		foreach ($this->get_outline_dispositions($outline) as $disposition)
		{
			HiddenCMS()->module('live_editor')->model()->delete_widgets($this->disposition->decode($disposition['disposition']));
		}

		$this->db	->where('outline_id', $outline_id)
					->update('pages', [
						'outline_id' => $base['outline_id']
					]);

		$this->db	->where('theme', $outline['theme'])
					->where('page', $this->outline_page($outline_id))
					->delete('dispositions');

		$this->db	->where('outline_id', $outline_id)
					->delete('outlines');

		return $this;
	}

	public function render_region($outline_id, $region)
	{
		if (!($outline = $this->get_outline($outline_id)) && !($outline = $this->get_outline()))
		{
			return '';
		}

		if (!($disposition = $this->get_region_disposition($outline, $region)) && !$outline['base'] && ($base = $this->get_outline()))
		{
			$disposition = $this->get_region_disposition($base, $region);
		}

		return $disposition ? $this->zone()->display($disposition) : '';
	}

	public function outline_page($outline_id)
	{
		return 'outline:'.(int)$outline_id;
	}

	private function create_dispositions($outline_id, $theme)
	{
		foreach ($this->theme_zones($theme) as $zone => $title)
		{
			if (!$this->db	->from('dispositions')
							->where('theme', $theme)
							->where('page', $this->outline_page($outline_id))
							->where('zone', $zone)
							->empty())
			{
				continue;
			}

			$this->db->insert('dispositions', [
				'theme'       => $theme,
				'page'        => $this->outline_page($outline_id),
				'zone'        => $zone,
				'disposition' => $this->disposition->encode($this->default_disposition($title))
			]);
		}

		return $this;
	}

	private function get_region_disposition($outline, $region)
	{
		$zone = $this->region_zone($outline['theme'], $region);

		if ($zone === NULL)
		{
			return FALSE;
		}

		return $this->db	->from('dispositions')
						->where('theme', $outline['theme'])
						->where('page', $this->outline_page($outline['outline_id']))
						->where('zone', $zone)
						->row();
	}

	private function get_outline_dispositions($outline)
	{
		return $this->db	->select('*')
						->from('dispositions')
						->where('theme', $outline['theme'])
						->where('page', $this->outline_page($outline['outline_id']))
						->get();
	}

	private function duplicate_disposition_widgets($disposition)
	{
		$disposition->each($traverse = function($item) use (&$traverse){
			if (is_a($item, 'HB\HiddenCMS\Displayables\Widget'))
			{
				$item->widget_id($this->db->insert('widgets', $this->db	->select('widget', 'type', 'title', 'settings')
																		->from('widgets')
																		->where('widget_id', $item->widget_id())
																		->row()));
			}
			else if ($item && method_exists($item, 'each'))
			{
				$item->each($traverse);
			}

			return $item;
		});

		return $disposition;
	}

	private function region_zone($theme, $region)
	{
		$theme = HiddenCMS()->theme($theme ?: $this->config->default_theme);
		$regions = $theme->regions();

		if (empty($regions[$region]))
		{
			return NULL;
		}

		$zone = array_search($regions[$region], $theme->info()->zones);

		return $zone === FALSE ? NULL : $zone;
	}

	private function theme_zones($theme)
	{
		return HiddenCMS()->theme($theme ?: $this->config->default_theme)->info()->zones;
	}

	private function default_disposition($zone_title)
	{
		if (url_title($zone_title) == 'contenu')
		{
			return $this->array([
				$this->row(
					$this->col(
						$this->widget($this->db->insert('widgets', [
							'widget' => 'module',
							'type'   => 'index'
						]))
					)
				)
			]);
		}

		return $this->array();
	}
}
