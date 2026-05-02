<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace NF\Widgets\Copyright;

use HB\HiddenCMS\Addons\Widget;

class Copyright extends Widget
{
	protected function __info()
	{
		return [
			'title'       => 'Copyright',
			'description' => '',
			'link'        => 'https://neofr.ag',
			'author'      => 'MichaÃ«l BILCOT & JÃ©rÃ©my VALENTIN <contact@neofrag.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'version'     => '1.0',
			'depends'     => [
				'neofrag' => 'Alpha 0.2'
			]
		];
	}
}


