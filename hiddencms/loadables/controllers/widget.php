<?php
/**
 * https://neofr.ag
 * @author: MichaÃƒÆ’Ã‚Â«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Loadables\Controllers;

use HB\HiddenCMS\Loadables\Controller;

abstract class Widget extends Controller
{
	abstract public function index($config = []);

	public function __construct($caller)
	{
		parent::__construct($this->widget = $caller);
	}

	public function title($title)
	{
		$this->widget->data->append('title', $title);
		return $this;
	}
}


