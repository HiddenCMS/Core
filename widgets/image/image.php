<?php

namespace HB\Widgets\Image;

use HB\HiddenCMS\Addons\Widget;

class Image extends Widget
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('Image'),
			'icon'        => 'far fa-image',
			'description' => $this->lang('Afficher une image de la médiathèque.'),
			'author'      => 'HiddenCMS',
			'license'     => 'GPLv3',
			'version'     => '1.0'
		];
	}
}
