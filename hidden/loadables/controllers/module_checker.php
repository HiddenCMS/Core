<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Hidden\Loadables\Controllers;

use HD\Hidden\Loadables\Controllers;

abstract class Module_Checker extends Module
{
	private $_extension_allowed;

	public function __construct($caller)
	{
		parent::__construct($caller);

		$this->_extension_allowed = !$this->url->extension;
	}

	public function extension($extension)
	{
		if ($this->url->extension != $extension)
		{
			$this->error();
		}
		else
		{
			$this->_extension_allowed = TRUE;
		}

		return $this;
	}

	public function valid()
	{
		return $this->_extension_allowed;
	}
}
