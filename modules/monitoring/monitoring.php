<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Modules\Monitoring;

use HD\Hidden\Addons\Module;

class Monitoring extends Module
{
	protected function __info()
	{
		return [
			'title'       => 'Monitoring',
			'description' => '',
			'icon'        => 'fas fa-heartbeat',
			'link'        => 'https://neofr.ag',
			'author'      => 'Michaël BILCOT & Jérémy VALENTIN <contact@hidden.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'admin'       => FALSE
		];
	}

	public function need_checking()
	{
		return ($this->config->monitoring_last_check < ($time = strtotime('01:00')) && time() > $time) || !file_exists('cache/monitoring/monitoring.json');
	}

	public function display()
	{
		if (file_exists('cache/monitoring/monitoring.json'))
		{
			foreach (array_merge(array_fill_keys(['danger', 'warning', 'info'], 0), array_count_values(array_map(function($a){
				return $a[1];
			}, json_decode(file_get_contents('cache/monitoring/monitoring.json'))->notifications))) as $class => $count)
			{
				if ($count)
				{
					return '<span class="float-right badge badge-'.$class.'">'.$count.'</span>';
				}
			}
		}

		return '';
	}
}
