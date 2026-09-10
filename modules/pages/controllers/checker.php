<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Pages\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module_Checker;

class Checker extends Module_Checker
{
	public function _index($name)
	{
		if ($this->url->segments[0] != 'pages' && ($resolved = $this->model()->resolve([$name])))
		{
			$content = $resolved['page'];

			if ($this->access('pages', 'access_page', $content['page_id']))
			{
				return $content;
			}

			return $this->error->unauthorized();
		}
	}
}


