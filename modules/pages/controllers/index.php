<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace NF\Modules\Pages\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Index extends Controller_Module
{
	public function _index($page, $title = NULL, $subtitle = NULL, $content = NULL)
	{
		if (is_array($page))
		{
			$page_id  = $page['page_id'];
			$title    = $page['title'];
			$subtitle = $page['subtitle'];
			$content  = $page['content'];
		}
		else
		{
			$page_id = $page;
		}

		$this	->title($title)
				->breadcrumb($title);

		return $this->panel()
					->heading($title.($subtitle ? ' <small>'.$subtitle.'</small>' : ''), 'far fa-file-alt')
					->body(bbcode($content));
	}
}


