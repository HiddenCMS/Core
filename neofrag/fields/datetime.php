<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace NF\NeoFrag\Fields;

#[\AllowDynamicProperties]
class Datetime
{
	public function init($field)
	{
		$field->default(HB()->date()->sql());
	}

	public function value($value)
	{
		if ($value)
		{
			return HB()->date($value);
		}
	}

	public function raw($value)
	{
		if (is_a($value, 'HB\HiddenCMS\Libraries\Date'))
		{
			$value = $value->sql();
		}

		return $value;
	}
}


