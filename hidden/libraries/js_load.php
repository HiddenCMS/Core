<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Hidden\Libraries;

use HD\Hidden\Library;

class Js_Load extends Library
{
	protected $_script;

	public function __invoke($script)
	{
		$this->_script = $script;

		$this->output->data->append('js_load', $this);

		return $this;
	}

	public function __toString()
	{
		return (string)$this->_script;
	}
}
