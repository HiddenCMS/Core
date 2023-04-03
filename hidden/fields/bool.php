<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Hidden\Fields;

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
