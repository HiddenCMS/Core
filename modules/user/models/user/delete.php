<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\User\Models\User;

class Delete extends \HB\HiddenCMS\Actions\Delete
{
	protected function check($user)
	{
		return !$user->deleted &&
			$this->access('user', 'delete_users') &&
			($this->user->admin || (!$user->admin && $user->id != $this->user->id));
	}
}


