<?php

namespace HB\Widgets\Media_Text;

use HB\HiddenCMS\Addons\Widget;

class Media_Text extends Widget
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('Image + texte'),
			'icon'        => 'fas fa-columns',
			'description' => $this->lang('Associer une image et un contenu éditorial.'),
			'author'      => 'HiddenCMS',
			'license'     => 'GPLv3',
			'version'     => '1.0'
		];
	}
}
