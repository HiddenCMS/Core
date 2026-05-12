<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Outlines\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module_Checker;

class Admin_Checker extends Module_Checker
{
	private function outline_model()
	{
		return $this->module->model2('outline');
	}

	public function index($page = '')
	{
		return [$this->module->pagination->get_data($this->outline_model()->get_outlines(), $page)];
	}

	public function add()
	{
		return [];
	}

	public function _edit($outline_id, $title)
	{
		if ($outline = $this->outline_model()->check_outline($outline_id, $title, TRUE))
		{
			return [
				$outline['outline_id'],
				$outline['name'],
				$outline['title'],
				$outline['theme'],
				$outline['base'],
				$outline['enabled']
			];
		}
	}

	public function _duplicate($outline_id)
	{
		if ($outline = $this->outline_model()->get_outline_by_id($outline_id))
		{
			return [$outline];
		}
	}

	public function _delete($outline_id)
	{
		if (($outline = $this->outline_model()->get_outline_by_id($outline_id)) && !$outline['base'])
		{
			return [$outline];
		}
	}
}
