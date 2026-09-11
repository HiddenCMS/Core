<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\User\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module_Checker;

class Ajax_Checker extends Module_Checker
{
	public function _member($id, $username)
	{
		if (($user = $this->model2('user', $id)->check($username)) && !$user->deleted)
		{
			return [$user];
		}
	}

}


