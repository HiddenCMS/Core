<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace UF\Modules\User\Controllers;

use UF\uFrag\Loadables\Controllers\Module as Controller_Module;

class Statistics extends Controller_Module
{
	public function statistics()
	{
		return [
			'registrations' => [
				'title' => 'Inscriptions',
				'data'  => function(){
					$this->db	->from('user')
								->where('deleted', FALSE);

					return 'registration_date';
				}
			],
			'sessions' => [
				'title'    => 'Connections de membres',
				'group_by' => 'COUNT(DISTINCT user_id)',
				'data'     => function(){
					$this->db->from('session_history');
					return 'date';
				}
			]
		];
	}
}
