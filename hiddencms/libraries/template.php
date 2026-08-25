<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries;

use HB\HiddenCMS\Library;

class Template extends Library
{
	protected $_component = '';
	protected $_data = [];
	protected $_fallback = '';
	protected $_owners = [];

	public function __invoke($component = '', array $data = [], $fallback = '')
	{
		if ($component !== '')
		{
			$this->_component = trim($component, '/');
		}

		$this->_data = $data;
		$this->_fallback = $fallback;
		$this->_owners = [];

		return $this;
	}

	public function owner($owner)
	{
		if ($owner)
		{
			$this->_owners[] = $owner;
		}

		return $this;
	}

	public function fallback($fallback)
	{
		$this->_fallback = $fallback;
		return $this;
	}

	public function render($component = '', array $data = [], $fallback = '')
	{
		return (string)$this($component, $data, $fallback);
	}

	public function __toString()
	{
		$paths = [];

		foreach ($this->owners() as $owner)
		{
			foreach ($this->candidate_files() as $file)
			{
				if ($owner->__path('views', $file, $paths))
				{
					return (string)$owner->view($file, $this->_data);
				}
			}
		}

		return (string)$this->_fallback;
	}

	private function owners()
	{
		if ($this->_owners)
		{
			return $this->_owners;
		}

		$owners = [];

		if ($theme = $this->output->theme())
		{
			$owners[] = $theme;
		}

		$owners[] = HB();

		return $owners;
	}

	private function candidate_files()
	{
		$component = trim((string)$this->_component);

		if ($component === '')
		{
			return [];
		}

		if (preg_match('/\.tpl\.php$/', $component))
		{
			return [$component];
		}

		if (strpos($component, 'components/') === 0)
		{
			return [$component.'.tpl.php'];
		}

		return [
			'components/'.$component.'.tpl.php'
		];
	}
}
