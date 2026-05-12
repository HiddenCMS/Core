<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries\Buttons;

use HB\HiddenCMS\Libraries\Button;

class Submit extends Button
{
	public function __invoke($title = '', $color = 'primary')
	{
		parent::__invoke();

		return $this->title($title ?: $this->lang('Valider'))
					->tag('button')
					->attr('type', 'submit')
					->color($color);
	}
}


