<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Hidden\Libraries\Buttons;

use HD\Hidden\Library;

class Back extends Library
{
	public function __invoke($url = '', $title = '')
	{
		return $this->button()
					->title($title ?: $this->lang('Retour'))
					->url($this->url->back() ?: $url)
					->color('secondary');
	}
}
