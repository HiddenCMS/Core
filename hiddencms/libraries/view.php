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

				if (substr($path, -5) == '.twig')
				{
					return $this->render_twig($path);
				}

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

		if (preg_match('/\.(?:twig|tpl\.php)$/', $name))
		{
			return [$name];
		}

		return [
			$name.'.tpl.php',
			$name.'.twig'
		];
	}

	private function render_php($path)
	{
		ob_start();

		extract($this->_data);

		include $path;

		return ob_get_clean();
	}

	private function render_twig($path)
	{
		if (!class_exists('\Twig\Environment'))
		{
			trigger_error('Twig is not installed. Please run composer install/update.', E_USER_WARNING);
			return '';
		}

		static $twig = [];

		$dir = dirname($path);

		if (!isset($twig[$dir]))
		{
			$loader = new \Twig\Loader\FilesystemLoader([$dir]);
			$twig[$dir] = new \Twig\Environment($loader, [
				'cache'       => FALSE,
				'autoescape'  => FALSE,
				'debug'       => FALSE,
				'strict_variables' => FALSE
			]);

			$twig[$dir]->addFunction(new \Twig\TwigFunction('icon', function($name){
				return icon($name);
			}));

			$twig[$dir]->addFunction(new \Twig\TwigFunction('url', function($value = ''){
				return url($value);
			}));

			$twig[$dir]->addFunction(new \Twig\TwigFunction('lang', function(...$args){
				return call_user_func_array([$this, 'lang'], $args);
			}));
		}

		return $twig[$dir]->render(basename($path), $this->_data);
	}
}


