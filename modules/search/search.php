<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Search;

use HB\HiddenCMS\Addons\Module;

class Search extends Module
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('Rechercher'),
			'description' => '',
			'icon'        => 'fas fa-search',
			'link'        => 'https://neofr.ag',
			'author'      => 'Michaël BILCOT & Jérémy VALENTIN <contact@HiddenCMS.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'front'       => TRUE,
			'routes'      => [
				'(?:(.+?){pages})?' => 'index'
			]
		];
	}
}


