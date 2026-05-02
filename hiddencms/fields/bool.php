<?php
/**
 * https://neofr.ag
 * @author: MichaÃƒÂ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Fields;

#[\AllowDynamicProperties]
class Bool_
{
	public function init($field)
	{
		$field->default('0');
	}

	public function value($value)
	{
		return (bool)$value;
	}

	public function raw($value)
	{
		return (string)(int)$value;
	}
}
