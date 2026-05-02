<?php
/**
 * https://neofr.ag
 * @author: MichaÃƒÆ’Ã‚Â«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Models;

use HB\HiddenCMS\Loadables\Model2;

class Session_History extends Model2
{
	static public function __schema()
	{
		return [
			'id'         => self::field()->primary(),
			'user'       => self::field()->depends('user/user'),
			'ip_address' => self::field()->text(39),
			'host_name'  => self::field()->text(100),
			'referer'    => self::field()->text(100),
			'user_agent' => self::field()->text(250),
			'auth'       => self::field()->serialized(),
			'date'       => self::field()->datetime()
		];
	}
}


