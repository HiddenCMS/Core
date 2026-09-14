<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries\Forms;

use HB\HiddenCMS\Core\Debug;

class File extends Labelable
{
	protected $_mimes = [];
	protected $_thumbnail;
	protected $_uploaded;
	protected $_precheck;
	protected $_temp;

	public function __invoke($name, $upload_dir = '')
	{
		$this->_template[] = function(&$input){
			$input = $this	->html('input', TRUE)
							->attr('type', 'file');

			if ($this->_disabled)
			{
				$input->attr('disabled');
			}
		};

		parent::__invoke($name);

		$this->_template[] = function(&$input){
			if ($this->_thumbnail)
			{
				$input = $this->template->render('forms/file', [
					'thumbnail' => call_user_func_array($this->_thumbnail, []),
					'input'     => $input
				], call_user_func_array($this->_thumbnail, []).$input);
			}
		};

		$this->_check[1] = function($post, &$data) use ($upload_dir){
			if (($this->_required && !$this->_value) || !empty($_FILES[$this->_name]['name']))
			{
				if (!empty($_FILES[$this->_name]['error']))
				{
					$errors = [
						1 => 'The uploaded file exceeds upload_max_filesize in php.ini',
						2 => 'The uploaded file exceeds MAX_FILE_SIZE in the HTML form',
						3 => 'The file was only partially uploaded',
						4 => 'No file was uploaded',
						6 => 'A temporary folder is missing',
						7 => 'Failed to write the file to disk',
						8 => 'A PHP extension stopped the file upload'
					];

					$this->_errors[] = $this->lang($errors[$_FILES[$this->_name]['error']] ?? 'Unknown upload error');
				}
				else if ($this->_mimes && !in_array($_FILES[$this->_name]['type'], $this->_mimes))
				{
					$this->_errors[] = HB()->lang('File type not allowed');
				}
				else if (!empty($_FILES[$this->_name]['tmp_name']))
				{
					if (($this->_precheck && call_user_func_array($this->_precheck, [$_FILES[$this->_name]['tmp_name']])) || $this->_temp)
					{
						$data[$this->_name] = $_FILES[$this->_name]['tmp_name'];
					}
					else if (!$this->_errors)
					{
						$data[$this->_name] = HB()->model2('file')->static_uploaded_file($_FILES[$this->_name], $upload_dir, $this->_value ? $this->_value->id : NULL);

						if ($data[$this->_name]->path())
						{
							if ($this->_uploaded)
							{
								call_user_func_array($this->_uploaded, [$data[$this->_name]]);
							}
						}
						else
						{
							$this->_errors[] = HB()->lang('Transfer error');
						}
					}
				}
			}
		};

		return $this;
	}

	public function value($file, $erase = FALSE)
	{
		if (!is_a($file, 'HB/HiddenCMS/Models/File') && ($file = HB()->model2('file', $file)) && !$file())
		{
			$file = NULL;
		}

		return parent::value($file, $erase);
	}

	public function mime($mime)
	{
		$this->_mimes[] = $mime;
		return $this;
	}

	public function uploaded($uploaded)
	{
		$this->_uploaded = $uploaded;
		return $this;
	}

	public function temp()
	{
		$this->_temp = TRUE;
		return $this;
	}
}
