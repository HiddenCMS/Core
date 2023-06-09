<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Widgets\News;

use HD\Hidden\Addons\Widget;

class News extends Widget
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('Actualités'),
			'description' => '',
			'link'        => 'https://neofr.ag',
			'author'      => 'Michaël BILCOT & Jérémy VALENTIN <contact@hidden.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'version'     => '1.0',
			'depends'     => [
				'hidden' => '0.0.1'
			],
			'types'       => [
				'index'      => $this->lang('Actualités récentes'),
				'categories' => $this->lang('Catégories'),
				'tags'       => $this->lang('Tags')
			]
		];
	}
}
