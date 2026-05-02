<?php
/**
 * https://neofr.ag
 * @author: JÃ©rÃ©my VALENTIN <jeremy.valentin@neofr.ag>
 */

namespace NF\Widgets\Socials;

use HB\HiddenCMS\Addons\Widget;

class Socials extends Widget
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('RÃ©seaux sociaux'),
			'description' => '',
			'link'        => 'https://neofr.ag',
			'author'      => 'JÃ©rÃ©my VALENTIN <jeremy.valentin@neofr.ag>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'version'     => '1.0',
			'depends'     => [
				'neofrag' => 'Alpha 0.2.2'
			]
		];
	}
}


