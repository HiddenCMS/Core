<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace NF\Widgets\News;

use HB\HiddenCMS\Addons\Widget;

class News extends Widget
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('ActualitÃ©s'),
			'description' => '',
			'link'        => 'https://neofr.ag',
			'author'      => 'MichaÃ«l BILCOT & JÃ©rÃ©my VALENTIN <contact@neofrag.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'version'     => '1.0',
			'depends'     => [
				'neofrag' => 'Alpha 0.2'
			],
			'types'       => [
				'index'      => $this->lang('ActualitÃ©s rÃ©centes'),
				'categories' => $this->lang('CatÃ©gories'),
				'tags'       => $this->lang('Tags')
			]
		];
	}
}


