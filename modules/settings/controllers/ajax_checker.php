<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Modules\Settings\Controllers;

use HD\Hidden\Loadables\Controllers\Module_Checker;

class Ajax_Checker extends Module_Checker
{
	public function humans()
	{
		if ($this->url->request == 'humans.txt' && $this->config->humans_txt)
		{
			$this->extension('txt');
			return [];
		}
	}

	public function robots()
	{
		if ($this->url->request == 'robots.txt' && $this->config->robots_txt)
		{
			$this->extension('txt');
			return [];
		}
	}
}
