<?php
/**
 * https://neofr.ag
 * @author: MichaÃƒÆ’Ã‚Â«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries;

use HB\HiddenCMS\Library;

class No_Translate extends Library
{
	protected $_value;

	public function __invoke($value)
	{
		$this->_value = $value;
		return $this;
	}

	public function __toString()
	{
		return (string)$this->_value;
	}
}


