<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Modules\Settings\Controllers;

use HD\Hidden\Loadables\Controllers\Module as Controller_Module;

class Admin_Ajax extends Controller_Module
{
	public function maintenance()
	{
		$this->config('maintenance', (bool)post('closed'), 'bool');

		return $this->json([
			'status' => $this->config->maintenance
		]);
	}
}
