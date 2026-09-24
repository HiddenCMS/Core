<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Statistics;

use HB\HiddenCMS\Addons\Module;

class Statistics extends Module
{
	public function permissions()
	{
		return [
			'default' => [
				'access' => [[
					'title'  => $this->lang('Statistics'),
					'icon'   => 'far fa-chart-bar',
					'access' => [
						'view_statistics' => [
							'title' => $this->lang('View statistics'),
							'icon'  => 'far fa-eye',
							'admin' => TRUE
						]
					]
				]]
			]
		];
	}

	protected function __info()
	{
		return [
			'title'       => $this->lang('Statistics'),
			'description' => '',
			'icon'        => 'far fa-chart-bar',
			'link'        => 'https://neofr.ag',
			'author'      => 'Michaël BILCOT & Jérémy VALENTIN <contact@HiddenCMS.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'admin'       => FALSE
		];
	}
}


