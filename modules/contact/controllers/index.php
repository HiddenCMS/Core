<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Contact\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Index extends Controller_Module
{
	public function index()
	{
		$this->css('front');

		return $this->view('index', [
			'form' => $this->form2('contact')->panel()
		]);
	}
}


