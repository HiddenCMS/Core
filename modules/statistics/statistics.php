<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace NF\Modules\Statistics;

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
			'author'      => 'MichaÃ«l BILCOT & JÃ©rÃ©my VALENTIN <contact@neofrag.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'admin'       => FALSE
		];
	}
}


