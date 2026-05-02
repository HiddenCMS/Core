<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Admin;

use HB\HiddenCMS\Addons\Module;

class Admin extends Module
{
	protected function __info()
	{
		return [
			'title'       => 'Tableau de bord',
			'description' => '',
			'icon'        => 'fas fa-tachometer-alt',
			'link'        => 'https://neofr.ag',
			'author'      => 'MichaÃ«l BILCOT & JÃ©rÃ©my VALENTIN <contact@HiddenCMS.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'admin'       => FALSE
		];
	}

	public function is_authorized()
	{
		return $this->access->admin();
	}
}


