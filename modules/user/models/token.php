<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Modules\User\Models;

use HD\Hidden\Loadables\Model2;

class Token extends Model2
{
	static public function __schema()
	{
		return [
			'id'   => self::field()->text(32)->primary(),
			'user' => self::field()->depends('user/user')
		];
	}
}
