<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Hidden\Fields;

class Enum
{
	protected $_values;

	public function __construct()
	{
		$this->_values = array_map('strval', func_get_args());
	}

	public function raw($value, $is_nullable)
	{
		$value = strval($value);

		if (!in_array($value, $this->_values, TRUE))
		{
			$value = $is_nullable ? NULL : reset($this->_values);
		}

		return $value;
	}
}
