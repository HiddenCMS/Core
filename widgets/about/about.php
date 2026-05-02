<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Widgets\About;

use HB\HiddenCMS\Addons\Widget;

class About extends Widget
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('Ã€ propos'),
			'description' => '',
			'link'        => 'https://neofr.ag',
			'author'      => 'MichaÃ«l BILCOT & JÃ©rÃ©my VALENTIN <contact@HiddenCMS.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'version'     => '1.0',
			'depends'     => [
				'HiddenCMS' => 'Alpha 0.2'
			]
		];
	}
}


