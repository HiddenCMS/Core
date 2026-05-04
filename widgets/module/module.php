<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Widgets\Module;

use HB\HiddenCMS\Addons\Widget;

class Module extends Widget
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('Contenu de page'),
			'description' => '',
			'link'        => 'https://neofr.ag',
			'author'      => 'Michaël BILCOT & Jérémy VALENTIN <contact@HiddenCMS.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>'
		];
	}
}


