<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Settings\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module_Checker;

class Admin_Ajax_Checker extends Module_Checker
{
	public function maintenance()
	{
		$this->extension('json');

		return [];
	}

	public function core_update()
	{
		return [];
	}

	public function addon_update($vendor, $name)
	{
		if (strtolower($vendor) === 'hiddencms' && preg_match('/^[a-z0-9](?:[a-z0-9_.-]*[a-z0-9])?$/i', $name))
		{
			return ['hiddencms', strtolower($name)];
		}
	}

	public function backup()
	{
		return [];
	}

	public function rollback($id)
	{
		if (preg_match('/^[a-z0-9-]+$/i', $id))
		{
			return [$id];
		}
	}
}


