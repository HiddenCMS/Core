<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Modules\Contact\Controllers;

use HD\Hidden\Loadables\Controllers\Module as Controller_Module;

class Ajax extends Controller_Module
{
	public function index()
	{
		return $this->form2('contact')
					->modal($this->lang('Nous contacter'), 'far fa-envelope')
					->cancel();
	}
}
