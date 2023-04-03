<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Hidden\Fields;

class Depends
{
	protected $_model;
	protected $_suffix;

	public function __construct($model, $suffix = '_id')
	{
		$this->_model  = explode('/', $model);
		$this->_suffix = $suffix;
	}

	public function key($key)
	{
		return $key.$this->_suffix;
	}

	public function raw($value)
	{
		return (is_a($value, 'HD\Hidden\Loadables\Model2') ? $value->id : $value) ?: NULL;
	}

	public function value($value)
	{
		if (is_a($value, 'HD\Hidden\Loadables\Model2'))
		{
			return $value;
		}

		if (isset($this->_model[1]))
		{
			$value = Hidden()->module($this->_model[0])->model2($this->_model[1], $value);
		}
		else
		{
			$value = Hidden()->model2($this->_model[0], $value);
		}

		return $value;
	}
}
