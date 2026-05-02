<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Statistics;

use HB\HiddenCMS\Addons\Module;

class Statistics extends Module
{
	protected function __info()
	{
		return [
			'title'       => 'Statistiques',
			'description' => '',
			'icon'        => 'far fa-chart-bar',
			'link'        => 'https://neofr.ag',
			'author'      => 'Michaël BILCOT & Jérémy VALENTIN <contact@HiddenCMS.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'admin'       => FALSE
		];
	}
}


