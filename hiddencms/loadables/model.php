<?php
/**
 * https://neofr.ag
 * @author: MichaÃƒÆ’Ã‚Â«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Loadables;

use HB\HiddenCMS\HiddenCMS;

abstract class Model extends HiddenCMS implements \HB\HiddenCMS\Loadable
{
	static protected $_objects = [];

	static public function __load($caller, $args = [])
	{
		$name = array_shift($args) ?: $caller->info()->name;

		if (!isset(static::$_objects[$caller_name = get_class($caller)][$name]))
		{
			static::$_objects[$caller_name][$name] = $caller->___load('models', $name, [$caller]);
		}

		return static::$_objects[$caller_name][$name];
	}

	public function __construct($caller)
	{
		$this->__caller = $caller;
	}
}


