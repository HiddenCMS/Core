<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Hidden\Fields;

class Datetime
{
	public function init($field)
	{
		$field->default(Hidden()->date()->sql());
	}

	public function value($value)
	{
		if ($value)
		{
			return Hidden()->date($value);
		}
	}

	public function raw($value)
	{
		if (is_a($value, 'HD\Hidden\Libraries\Date'))
		{
			$value = $value->sql();
		}

		return $value;
	}
}
