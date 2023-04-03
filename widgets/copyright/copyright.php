<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Widgets\Copyright;

use HD\Hidden\Addons\Widget;

class Copyright extends Widget
{
	protected function __info()
	{
		return [
			'title'       => 'Copyright',
			'description' => '',
			'link'        => 'https://neofr.ag',
			'author'      => 'Michaël BILCOT & Jérémy VALENTIN <contact@hidden.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'version'     => '1.0',
			'depends'     => [
				'hidden' => '0.0.1'
			]
		];
	}
}
