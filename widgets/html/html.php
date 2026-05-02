<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace NF\Widgets\Html;

use HB\HiddenCMS\Addons\Widget;

class Html extends Widget
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('Contenu libre / Code HTML'),
			'description' => '',
			'link'        => 'https://neofr.ag',
			'author'      => 'MichaÃ«l BILCOT & JÃ©rÃ©my VALENTIN <contact@neofrag.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'types'       => [
				'index' => $this->lang('Contenu libre'),
				'html'  => $this->lang('Code HTML')
			]
		];
	}
}


