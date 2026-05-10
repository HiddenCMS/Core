<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Widgets\Navigation\Controllers;

use HB\HiddenCMS\Loadables\Controller;

class Checker extends Controller
{
	private function normalize_settings($settings = [])
	{
		$output = [
			'menu_id' => !empty($settings['menu_id']) ? (int)$settings['menu_id'] : 0,
			'panel'   => isset($settings['panel']) ? (bool)$settings['panel'] : TRUE
		];

		// Legacy fallback: keep old static links format only when no menu is selected.
		if (empty($output['menu_id']))
		{
			$links = [];

			foreach ($settings as $key => $values)
			{
				if (in_array($key, ['title', 'url', 'target']))
				{
					foreach ($values as $i => $value)
					{
						$links[$i][$key] = utf8_htmlentities($value);
					}
				}
			}

			if ($links)
			{
				$output['links'] = $links;
			}
		}

		return $output;
	}

	public function index($settings = [])
	{
		return $this->normalize_settings($settings);
	}

	public function vertical($settings = [])
	{
		return $this->normalize_settings($settings);
	}
}


