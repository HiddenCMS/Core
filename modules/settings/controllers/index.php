<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Modules\Settings\Controllers;

use HD\Hidden\Loadables\Controllers\Module as Controller_Module;

class Index extends Controller_Module
{
	public function maintenance()
	{
		if (!$this->url->maintenance)
		{
			$this->error();
		}

		return $this->title($this->config->maintenance_title ?: $this->lang('Site en maintenance'))
					->css('maintenance')
					->exec_if($this->config->maintenance_opening, function(){
						$this	->css('jquery.countdown')
								->js('jquery.countdown')
								->js('maintenance');
					})
					->view('maintenance');
	}
}
