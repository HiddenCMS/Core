<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Fields;

#[\AllowDynamicProperties]
class Primary
{
	public function init($field)
	{
		if (!$field->is_text() && !$field->is_depends())
		{
			$field->int();
		}
	}
}
