<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Hidden\Fields;

class Date extends DateTime
{
	public function raw($value)
	{
		return substr(parent::raw($value), 0, 10);
	}
}
