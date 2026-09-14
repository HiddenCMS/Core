<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries\Modals;

use HB\HiddenCMS\Libraries\Modal;

class Delete extends Modal
{
	public function __invoke($title = '', $icon = '')
	{
		return parent	::__invoke($title ?: (string)$this->lang('Confirm deletion'), ($icon ?: 'fas fa-trash-alt').' text-danger')
						->submit((string)$this->lang('Delete'), 'danger')
						->cancel();
	}
}


