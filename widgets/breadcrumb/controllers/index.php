<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Widgets\Breadcrumb\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Widget as Controller_Widget;

class Index extends Controller_Widget
{
	public function index($config = [])
	{
		$links = $this->output->data->get('breadcrumb') ?: [];

		if (empty($links) && $this->url->segments[0] == 'index')
		{
			array_unshift($links, [$this->lang('Accueil'), '', 'fas fa-map-marker-alt']);
		}
		else if ($this->output->module() && $this->output->module()->info()->name != 'pages')
		{
			array_unshift($links, [$this->output->module()->info()->title, $this->output->module()->info()->name, $this->output->module()->info()->icon ?: 'fas fa-map-marker-alt']);
		}

		$count = count($links);

		array_walk($links, function(&$value, $key) use ($count){
			$value = $this->html('li')
							->attr('class', 'breadcrumb-item')
							->append_attr_if($is_last = $key == $count - 1, 'class', 'active')
							->content('<a href="'.url($value[1]).'">'.($is_last && $value[2] !== '' ? icon($value[2]).' ' : '').$value[0].'</a>');
		});

		return '<ol class="breadcrumb"><li class="breadcrumb-item"><a href="'.url().'"><b>'.$this->config->name.'</b></a></li>'.implode($links).'</ol>';
	}
}


