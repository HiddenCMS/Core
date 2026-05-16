<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries;

use HB\HiddenCMS\Library;

class View extends Library
{
	protected $_name;
	protected $_path;
	protected $_data;

	public function __invoke($name, $data = [])
	{
		if (is_object($name))
		{
			return $name;
		}

		$this->_name = $name;
		$this->_data = $data;

		return $this;
	}

	public function __toString()
	{
		$paths = [];

		foreach ($this->candidate_files() as $file)
		{
			if ($path = $this->__caller->__path('views', $file, $paths))
			{
				$this->_path = $path;

				return $this->render_php($path);
			}
		}

		trigger_error('Unfound view: '.$this->_name.' in paths ['.implode(';', $paths).']', E_USER_WARNING);

		return '';
	}

	public function path()
	{
		return $this->_path;
	}

	private function candidate_files()
	{
		$name = trim((string)$this->_name);

		if (preg_match('/\.tpl\.php$/', $name))
		{
			return [$name];
		}

		return [
			$name.'.tpl.php'
		];
	}

	private function render_php($path)
	{
		ob_start();

		extract($this->_data);

		include $path;

		return ob_get_clean();
	}
}


