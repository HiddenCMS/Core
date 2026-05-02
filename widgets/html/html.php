<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Widgets\Html;

use HB\HiddenCMS\Addons\Widget;

class Html extends Widget
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('Contenu libre / Code HTML'),
			'description' => '',
			'link'        => 'https://neofr.ag',
			'author'      => 'Michaël BILCOT & Jérémy VALENTIN <contact@HiddenCMS.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'types'       => [
				'index' => $this->lang('Contenu libre'),
				'html'  => $this->lang('Code HTML')
			]
		];
	}
}


