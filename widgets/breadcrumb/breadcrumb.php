<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Widgets\Breadcrumb;

use HD\Hidden\Addons\Widget;

class Breadcrumb extends Widget
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('Fil d\'Ariane'),
			'description' => '',
			'link'        => 'https://neofr.ag',
			'author'      => 'Michaël BILCOT & Jérémy VALENTIN <contact@hidden.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>'
		];
	}
}
