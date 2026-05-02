<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace NF\Modules\Settings\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module_Checker;

class Admin_Ajax_Checker extends Module_Checker
{
	public function maintenance()
	{
		$this->extension('json');

		return [];
	}
}


