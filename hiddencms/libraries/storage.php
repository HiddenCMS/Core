<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries;

use HB\HiddenCMS\Library;

class Storage extends Library
{
	public function encode($value)
	{
		$this->normalize($value);

		return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	}

	public function decode($value, $default = [])
	{
		if ($value === NULL || $value === '')
		{
			return $default;
		}

		$value = json_decode($value, TRUE);

		return json_last_error() == JSON_ERROR_NONE ? $value : $default;
	}

	public function key($value)
	{
		return hash('sha256', $this->encode($value));
	}

	private function normalize(&$value)
	{
		if ((is_string($value) || is_object($value)) && method_exists($value, '__toArray'))
		{
			$value = $value->__toArray();
		}
		else if (is_a($value, 'HB\HiddenCMS\Libraries\Date'))
		{
			$value = $value->sql();
		}

		if (is_array($value))
		{
			array_walk($value, function(&$item){
				$this->normalize($item);
			});
		}
	}
}
