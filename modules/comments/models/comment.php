<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Modules\Comments\Models;

use HD\Hidden\Loadables\Model2;

class Comment extends Model2
{
	public $__table = 'comment';

	static public function __schema()
	{
		return [
			'id'        => self::field()->primary(),
			'parent'    => self::field()->depends('comments/comment')->null(),
			'user'      => self::field()->depends('user/user')->default(Hidden()->user),
			'module_id' => self::field()->int(),
			'module'    => self::field()->text(100),
			'content'   => self::field()->text(),
			'date'      => self::field()->datetime()
		];
	}
}
