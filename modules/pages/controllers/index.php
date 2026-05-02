<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Pages\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Index extends Controller_Module
{
	public function _index($page, $blocks = [], $title = NULL, $subtitle = NULL, $content = NULL)
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

			if (!is_array($blocks))
			{
				$content  = $subtitle;
				$subtitle = $title;
				$title    = $blocks;
				$blocks   = [];
			}
		}

		$this	->title($title)
				->breadcrumb($title);

		$output = $this->array();

		if ($content !== '')
		{
			$output->append($this->static_block($title, $subtitle, $content));
		}

		foreach ($blocks as $block)
		{
			$output->append($this->block($block));
		}

		if ($output->empty())
		{
			$output->append($this->static_block($title, $subtitle, ''));
		}

		return $output;
	}

	public function blocks($blocks)
	{
		$output = $this->array();

		foreach ($blocks as $block)
		{
			$output->append($this->block($block));
		}

		return $output;
	}

	private function block($block)
	{
		if (empty($block['module']))
		{
			return $this->static_block(NULL, NULL, isset($block['settings']['content']) ? $block['settings']['content'] : '');
		}

		return HB()->output->module_content($block['module'], strtoarray('/', $block['route']), FALSE);
	}

	private function static_block($title, $subtitle, $content)
	{
		$panel = $this->panel();

		if ($title !== NULL)
		{
			$panel->heading($title.($subtitle ? ' <small>'.$subtitle.'</small>' : ''), 'far fa-file-alt');
		}

		return $panel->body(bbcode($content));
	}
}


