<?php
/**
 * https://neofr.ag
 * @author: MichaÃƒÆ’Ã‚Â«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Models;

use HB\HiddenCMS\Loadables\Model2;

class Session extends Model2
{
	static public function __schema()
	{
		return [
			'id'            => self::field()->text(32)->primary(),
			'user'          => self::field()->depends('user/user')->null(),
			'remember'      => self::field()->bool(),
			'last_activity' => self::field()->datetime(),
			'data'          => self::field()->serialized()
		];
	}
}


