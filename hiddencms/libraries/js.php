<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries;

use HB\HiddenCMS\Library;

class Js extends Library
{
	protected $_file;

	public function __invoke($file)
	{
		$this->_file = $file;

		$this->output->data->append('js', $this);

		return $this;
	}

	public function __toString()
	{
		return '<script type="text/javascript" src="'.$this->path().'"></script>';
	}

	public function path()
	{
		if (is_valid_url($this->_file))
		{
			$path = $this->_file;
		}
		else
		{
			$file = $this->_file.'.js';
			$path = path($file, 'js', $this->__caller);
			$asset = $this->__caller->__path('assets', 'js/'.$file);
			$version = $asset ? (int)filemtime($asset) : 0;

			if ($v = max((int)$this->config->version_css, $version))
			{
				$path .= '?v='.$v;
			}
		}

		return $path;
	}
}


