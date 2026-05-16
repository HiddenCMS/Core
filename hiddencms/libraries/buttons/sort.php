<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries\Buttons;

use HB\HiddenCMS\Library;

class Sort extends Library
{
	public function __invoke($id, $url, $parent = 'tbody', $items = 'tr')
	{
		return $this->js('https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js')
					->js('sortable')
					->button()
					->component('buttons/sort')
					->tooltip($this->lang('Ordonner'))
					->icon('fas fa-arrows-alt-v')
					->class('btn-sortable')
					->data([
						'id'     => $id,
						'update' => url($url),
						'parent' => $parent,
						'items'  => $items
					]);
	}
}


