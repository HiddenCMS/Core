<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Hidden\Fields;

class Serialized
{
	public function init($field)
	{
		$field->default('');
	}

	public function value($value)
	{
		if (is_a($value, 'HD\Hidden\Libraries\Array_'))
		{
			return $value;
		}

		return $value ? Hidden()->array(unserialize($value)) : Hidden()->array;
	}

	public function raw($value)
	{
		$convert = function(&$value) use (&$convert){
			if ((is_string($value) || is_object($value)) && method_exists($value, '__toArray'))
			{
				$value = $value->__toArray();

				array_walk($value, $convert);
			}
			else if (is_a($value, 'HD\Hidden\Libraries\Date'))
			{
				$value = $value->sql();
			}
		};

		$convert($value);

		return $value ? serialize($value) : '';
	}
}
