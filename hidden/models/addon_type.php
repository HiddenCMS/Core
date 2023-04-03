<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Hidden\Models;

use HD\Hidden\Loadables\Model2;

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
