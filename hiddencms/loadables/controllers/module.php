<?php
/**
 * https://neofr.ag
 * @author: MichaÃƒÆ’Ã‚Â«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Loadables\Controllers;

use HB\HiddenCMS\Loadables\Controller;

abstract class Module extends Controller
{
	public function __construct($caller)
	{
		parent::__construct($this->module = $caller);
	}

	public function __call($name, $args)
	{
		if (method_exists($this->module, $name))
		{
			call_user_func_array([$this->module, $name], $args);
			return $this;
		}

		return parent::__call($name, $args);
	}
}


