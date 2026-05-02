<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace NF\Widgets\Search;

use HB\HiddenCMS\Addons\Widget;

class Search extends Widget
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('Rechercher'),
			'description' => '',
			'link'        => 'https://neofr.ag',
			'author'      => 'MichaÃ«l BILCOT & JÃ©rÃ©my VALENTIN <contact@neofrag.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>'
		];
	}
}


