<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Widgets\Header;

use HD\Hidden\Addons\Widget;

class Header extends Widget
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('Header'),
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
