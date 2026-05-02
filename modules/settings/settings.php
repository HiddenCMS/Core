<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Settings;

use HB\HiddenCMS\Addons\Module;

class Settings extends Module
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('Paramètres'),
			'description' => '',
			'icon'        => 'fas fa-cogs',
			'link'        => 'https://neofr.ag',
			'author'      => 'Michaël BILCOT & Jérémy VALENTIN <contact@HiddenCMS.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'admin'       => FALSE
		];
	}
}


