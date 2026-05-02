<?php
/**
 * https://neofr.ag
 * @author: MichaÃƒÆ’Ã‚Â«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries\Modals;

use HB\HiddenCMS\Libraries\Modal;

class Delete extends Modal
{
	public function __invoke($title = '', $icon = '')
	{
		return parent	::__invoke($title ?: 'Confirmation de suppression', ($icon ?: 'fas fa-trash-alt').' text-danger')
						->submit('Supprimer', 'danger')
						->cancel();
	}
}


