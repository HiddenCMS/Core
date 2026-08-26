<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries;

use HB\HiddenCMS\Library;

class Css extends Library
{
	protected $_file;
	protected $_media;

	public function __invoke($file, $media = '')
	{
		$this->_file  = $file;
		$this->_media = $media;

		$this->output->data->append('css', $this);

		return $this;
	}

	public function __toString()
	{
		if (is_valid_url($this->_file))
		{
			$path = $this->_file;
		}
		else
		{
			$file = $this->_file.'.css';
			$path = path($file, 'css', $this->__caller);
			$asset = $this->__caller->__path('assets', 'css/'.$file);
			$version = $asset ? (int)filemtime($asset) : 0;

			if ($v = max((int)$this->config->version_css, $version))
			{
				$path .= '?v='.$v;
			}
		}

		return $this->html('link', TRUE)
					->attr('rel',  'stylesheet')
					->attr('href', $path)
					->attr('type', 'text/css')
					->attr_if(!is_empty($this->_media), 'media', $this->_media)
					->__toString();
	}
}


