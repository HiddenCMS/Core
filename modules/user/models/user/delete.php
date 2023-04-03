<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Modules\User\Models\User;

class Delete extends \HD\Hidden\Actions\Delete
{
	protected function check($user)
	{
		return !$user->deleted;
	}
}
