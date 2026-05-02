<?php
/**
 * https://neofr.ag
 * @author: MichaÃƒÂ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Fields;

#[\AllowDynamicProperties]
class Float_
{
	public function init($field)
	{
		$field->default(0);
	}

	public function value($value)
	{
		return (float)$value;
	}

	public function raw($value)
	{
		return (float)$value;
	}
}
