<?php

namespace HB\Widgets\Hero;

use HB\HiddenCMS\Addons\Widget;

class Hero extends Widget
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('Hero'),
			'icon'        => 'fas fa-panorama',
			'description' => $this->lang('Créer une introduction visuelle avec appel à l’action.'),
			'author'      => 'HiddenCMS',
			'license'     => 'GPLv3',
			'version'     => '1.0'
		];
	}
}
