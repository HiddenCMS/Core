<?php

namespace HB\Widgets\Media_Text;

use HB\HiddenCMS\Addons\Widget;

class Media_Text extends Widget
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('Image + text'),
			'icon'        => 'fas fa-columns',
			'description' => $this->lang('Combine an image with editorial content.'),
			'author'      => 'HiddenCMS',
			'license'     => 'GPLv3',
			'version'     => '1.0'
		];
	}
}
