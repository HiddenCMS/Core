<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Hidden\Libraries\Modals;

use HD\Hidden\Libraries\Modal;

class Delete extends Modal
{
	public function __invoke($title = '', $icon = '')
	{
		return parent	::__invoke($title ?: 'Confirmation de suppression', ($icon ?: 'fas fa-trash-alt').' text-danger')
						->submit('Supprimer', 'danger')
						->cancel();
	}
}
