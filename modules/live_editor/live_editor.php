<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Live_Editor;

use HB\HiddenCMS\Addons\Module;

class Live_Editor extends Module
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('Live Editor'),
			'description' => '',
			'icon'        => 'fas fa-desktop',
			'link'        => 'https://neofr.ag',
			'author'      => 'Michaël BILCOT & Jérémy VALENTIN <contact@HiddenCMS.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'admin'       => FALSE
		];
	}
}


