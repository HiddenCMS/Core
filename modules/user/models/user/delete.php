<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace NF\Modules\User\Models\User;

class Delete extends \HB\HiddenCMS\Actions\Delete
{
	protected function check($user)
	{
		return !$user->deleted;
	}
}


