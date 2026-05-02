<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Widgets\Navigation;

use HB\HiddenCMS\Addons\Widget;

class Navigation extends Widget
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('Navigation'),
			'description' => '',
			'link'        => 'https://neofr.ag',
			'author'      => 'Michaël BILCOT & Jérémy VALENTIN <contact@HiddenCMS.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'version'     => '1.0',
			'depends'     => [
				'HiddenCMS' => 'Alpha 0.2'
			],
			'types'       => [
				'index'      => 'Horizontal',
				'vertical'   => 'Vertical'
			]
		];
	}
}


