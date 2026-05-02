<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace NF\Widgets\Breadcrumb;

use HB\HiddenCMS\Addons\Widget;

class Breadcrumb extends Widget
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('Fil d\'Ariane'),
			'description' => '',
			'link'        => 'https://neofr.ag',
			'author'      => 'MichaÃ«l BILCOT & JÃ©rÃ©my VALENTIN <contact@neofrag.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>'
		];
	}
}


