<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Hidden\Libraries\Buttons;

use HD\Hidden\Library;

class Sort extends Library
{
	public function __invoke($id, $url, $parent = 'tbody', $items = 'tr')
	{
		return $this->js('jquery-ui.min')
					->js('sortable')
					->button()
					->tooltip($this->lang('Ordonner'))
					->icon('fas fa-arrows-alt-v')
					->color('link')
					->style('btn-sortable')
					->data([
						'id'     => $id,
						'update' => url($url),
						'parent' => $parent,
						'items'  => $items
					])
					->compact()
					->outline();
	}
}
