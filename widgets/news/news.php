<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Widgets\News;

use HB\HiddenCMS\Addons\Widget;

class News extends Widget
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('ActualitÃ©s'),
			'description' => '',
			'link'        => 'https://neofr.ag',
			'author'      => 'MichaÃ«l BILCOT & JÃ©rÃ©my VALENTIN <contact@HiddenCMS.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'version'     => '1.0',
			'depends'     => [
				'HiddenCMS' => 'Alpha 0.2'
			],
			'types'       => [
				'index'      => $this->lang('ActualitÃ©s rÃ©centes'),
				'categories' => $this->lang('CatÃ©gories'),
				'tags'       => $this->lang('Tags')
			]
		];
	}
}


