<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace NF\Widgets\User;

use HB\HiddenCMS\Addons\Widget;

class User extends Widget
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('Espace membre'),
			'description' => '',
			'link'        => 'https://neofr.ag',
			'author'      => 'MichaÃ«l BILCOT & JÃ©rÃ©my VALENTIN <contact@neofrag.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'types'       => [
				'index'          => $this->lang('Espace membre'),
				'index_mini'     => $this->lang('Espace membre (mini)'),
				'messages_inbox' => $this->lang('Messagerie')
			]
		];
	}
}


