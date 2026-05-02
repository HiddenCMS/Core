<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Widgets\Copyright\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Widget as Controller_Widget;

class Index extends Controller_Widget
{
	public function index($settings = [])
	{
		$keywords = [
			'name'      => '<a href="'.url().'">'.$this->config->name.'</a>',
			'hiddencms' => '<a href="https://github.com/HiddenCMS/Core">HiddenCMS</a>',
			'HiddenCMS'   => '<a href="https://github.com/HiddenCMS/Core">HiddenCMS</a>',
			'year'      => date('Y'),
			'copyright' => icon('far fa-copyright')
		];

		if (!in_string('{hiddencms}', $copyright = utf8_html_entity_decode($this->config->copyright)) && !in_string('{HiddenCMS}', $copyright))
		{
			$copyright .= '<div class="float-right">'.$this->lang('PropulsÃ© par %s', '{hiddencms}').'</div>';
		}

		return $this->panel()
					->body(preg_replace_callback('/\{('.implode('|', array_keys($keywords)).')\}/i', function($match) use ($keywords){
						return $keywords[$match[1]];
					}, $copyright));
	}
}


