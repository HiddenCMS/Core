<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace NF\Modules\Settings;

use HB\HiddenCMS\Addons\Module;

class Settings extends Module
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('ParamÃ¨tres'),
			'description' => '',
			'icon'        => 'fas fa-cogs',
			'link'        => 'https://neofr.ag',
			'author'      => 'MichaÃ«l BILCOT & JÃ©rÃ©my VALENTIN <contact@neofrag.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'admin'       => FALSE
		];
	}
}


