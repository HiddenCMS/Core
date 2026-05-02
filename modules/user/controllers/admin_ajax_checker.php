<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\User\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module_Checker;

class Admin_Ajax_Checker extends Module_Checker
{
	public function _groups_sort()
	{
		if (($check = post_check('id', 'position')) && ($group = $this->groups->check_group([$check['id']])) && $group['auto'] != 'HiddenCMS')
		{
			return $check;
		}
	}
}


