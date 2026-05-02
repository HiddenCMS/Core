<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Contact;

use HB\HiddenCMS\Addons\Module;

class Contact extends Module
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('Contact'),
			'description' => '',
			'icon'        => 'far fa-envelope',
			'link'        => 'https://neofr.ag',
			'author'      => 'MichaÃ«l BILCOT & JÃ©rÃ©my VALENTIN <contact@HiddenCMS.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'front'       => TRUE,
			'version'     => '1.0',
			'depends'     => [
				'HiddenCMS' => 'Alpha 0.2'
			]
		];
	}
}


