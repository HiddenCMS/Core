<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Modules\User\Controllers;

use HD\Hidden\Loadables\Controllers\Module_Checker;

class Admin_Ajax_Checker extends Module_Checker
{
	public function _groups_sort()
	{
		if (($check = post_check('id', 'position')) && ($group = $this->groups->check_group([$check['id']])) && $group['auto'] != 'hidden')
		{
			return $check;
		}
	}
}
