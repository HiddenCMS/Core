<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Fields;

#[\AllowDynamicProperties]
class Json
{
	public function init($field)
	{
		$field->default('');
	}

	public function value($value)
	{
		if (is_a($value, 'HB\HiddenCMS\Libraries\Array_'))
		{
			return $value;
		}

		return HB()->array(HB()->storage->decode($value));
	}

	public function raw($value)
	{
		return $value ? HB()->storage->encode($value) : '';
	}
}
