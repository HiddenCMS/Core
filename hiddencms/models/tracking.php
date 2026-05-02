<?php
/**
 * https://neofr.ag
 * @author: MichaÃƒÆ’Ã‚Â«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Models;

use HB\HiddenCMS\Loadables\Model2;

class Tracking extends Model2
{
	static public function __schema()
	{
		return [
			'id'       => self::field()->primary(),
			'user'     => self::field()->depends('user/user')->default(HB()->user),
			'model'    => self::field()->text(100)->null(),
			'model_id' => self::field()->int()->null(),
			'date'     => self::field()->datetime()
		];
	}
}


