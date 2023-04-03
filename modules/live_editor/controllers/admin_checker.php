<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Modules\Live_Editor\Controllers;

use HD\Hidden\Loadables\Controllers\Module_Checker;

class Admin_Checker extends Module_Checker
{
	public function index()
	{
		if (!$this->user->admin)
		{
			$this->error->unauthorized();
		}

		return [];
	}
}
