<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Hidden\Fields;

class Int_
{
	public function init($field)
	{
		$field->default(0);
	}

	public function value($value)
	{
		return (int)$value;
	}

	public function raw($value)
	{
		return (int)$value;
	}
}
