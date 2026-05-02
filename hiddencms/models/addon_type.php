<?php
/**
 * https://neofr.ag
 * @author: MichaÃƒÆ’Ã‚Â«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Models;

use HB\HiddenCMS\Loadables\Model2;

class Addon_Type extends Model2
{
	static public function __schema()
	{
		return [
			'id'   => self::field()->primary(),
			'name' => self::field()->text(100)
		];
	}
}


