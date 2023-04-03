<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Hidden\Fields;

class Text
{
	protected $_size;

	public function __construct($size = NULL)
	{
		$this->_size = $size;
	}

	public function init($field)
	{
		$field->default('');
	}

	public function value($value)
	{
		return (string)$value;
	}
}
