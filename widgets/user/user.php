<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Widgets\User;

use HB\HiddenCMS\Addons\Widget;

class User extends Widget
{
	protected function __info()
	{
		return [
			'title'       => $this->lang('Member area'),
			'icon'        => 'fas fa-user',
			'description' => '',
			'link'        => 'https://neofr.ag',
			'author'      => 'Michaël BILCOT & Jérémy VALENTIN <contact@HiddenCMS.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'types'       => [
				'index'          => $this->lang('Member area'),
				'index_mini'     => $this->lang('Space (mini) Member'),
				'messages_inbox' => $this->lang('Messaging')
			]
		];
	}
}


