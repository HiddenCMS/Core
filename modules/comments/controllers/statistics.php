<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Comments\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Statistics extends Controller_Module
{
	public function statistics()
	{
		return [
			'comments' => [
				'title' => 'Commentaires',
				'data'  => function(){
					$this->db->from('comment');
					return 'date';
				}
			]
		];
	}
}


