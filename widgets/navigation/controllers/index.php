<?php
/**
 * https://neofr.ag
 * @author: Michael BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Widgets\Navigation\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Widget as Controller_Widget;

class Index extends Controller_Widget
{
	const MAX_SUBLEVELS = 3;

	public function index($settings = [])
	{
		return $this->_display($settings, 'horizontal', !empty($settings['panel']));
	}

	public function vertical($settings = [])
	{
		return $this->_display($settings, 'vertical', !isset($settings['panel']) || $settings['panel']);
	}

	protected function _display($settings, $type, $panel)
	{
		$this->js('navigation');

		if (empty($settings['links']) && !empty($settings['menu_id']) && ($module = @HiddenCMS()->module('menu')) && $module->is_enabled())
		{
			$settings['links'] = $module->model2('menu')->get_menu_links((int)$settings['menu_id']);
		}

		if (empty($settings['links']) || !is_array($settings['links']))
		{
			$settings['links'] = [];
		}

		$normalize = function($links) use (&$normalize){
			$output = [];

			foreach ($links as $link)
			{
				if (!is_array($link))
				{
					continue;
				}

				$link = array_merge([
					'title'  => '',
					'url'    => '',
					'icon'   => '',
					'access' => TRUE,
					'target' => ''
				], $link);

				if (is_array($link['url']))
				{
					$link['url'] = $normalize($link['url']);
				}

				$output[] = $link;
			}

			return $output;
		};

		$links = $normalize($settings['links']);

		$is_external = function($url){
			if (!is_string($url))
			{
				return FALSE;
			}

			return strpos($url, '#') === 0 || strpos($url, 'mailto:') === 0 || preg_match('#^(?:https?:)?//#i', $url);
		};

		$link_href = function($url) use ($is_external){
			return $is_external($url) ? $url : url($url);
		};

		$is_active = function($url) use ($is_external){
			if (!is_string($url) || $is_external($url))
			{
				return FALSE;
			}

			$clean = ltrim(preg_replace('_^'.preg_quote(url(), '_').'_', '', url($url)), '/');

			return ($clean == $this->url->request) || ($clean && strpos($this->url->request, $clean) === 0);
		};

		$actives = [];

		$collect_actives = function($items) use (&$collect_actives, &$actives, $is_active){
			foreach ($items as $item)
			{
				if (is_array($item['url']))
				{
					$collect_actives($item['url']);
				}
				else if ($is_active($item['url']))
				{
					$actives[] = $item['url'];
				}
			}
		};

		$collect_actives($links);

		usort($actives, function($a, $b){
			return strlen($b) <=> strlen($a);
		});

		$nav_link = function($item, $active) use ($link_href){
			return $this->html('a')
						->attr('class', 'nav-link')
						->append_attr_if($active, 'class', 'active')
						->exec(function($a) use ($item, $link_href){
							if (isset($item['modal']))
							{
								$this->js('modal');

								$a	->attr('href', '#')
									->attr('data-modal-ajax', url($item['modal']));
							}
							else
							{
								$a->attr('href', is_array($item['url']) ? '#' : $link_href($item['url']));

								if (!empty($item['target']))
								{
									$a->attr('target', $item['target']);
								}
							}
						})
						->content(icon($item['icon']).' '.$this->lang($item['title']));
		};

		$render_items = function($items, $depth = 0) use (&$render_items, $nav_link, $actives){
			if ($depth > self::MAX_SUBLEVELS)
			{
				return ['', FALSE];
			}

			$html = '';
			$branch_active = FALSE;

			foreach ($items as $item)
			{
				if (!$item['access'])
				{
					continue;
				}

				$current_active = FALSE;
				$children_html = '';

				if (is_array($item['url']))
				{
					list($children_html, $children_active) = $render_items($item['url'], $depth + 1);
					$current_active = $children_active;
				}
				else if ($actives && $actives[0] == $item['url'])
				{
					$current_active = TRUE;
				}

				$branch_active = $branch_active || $current_active;

				$link_html = $nav_link($item, $current_active);

				if ($children_html !== '')
				{
					$link_html	->attr('data-toggle', 'collapse')
								->attr('href', '#')
								->content(icon($item['icon']).' <span class="hidden-xs">'.$this->lang($item['title']).'</span><span class="fas fa-angle-down"></span>');

					$html .= $this->html('li')
								->attr('class', 'nav-item')
								->content($link_html.'<ul class="nav flex-column">'.$children_html.'</ul>');
				}
				else
				{
					$html .= $this->html('li')
								->attr('class', 'nav-item')
								->content($link_html);
				}
			}

			return [$html, $branch_active];
		};

		list($content) = $render_items($links);

		$nav = $this->html('ul')
					->attr('class', 'nav')
					->append_attr_if($type == 'vertical', 'class', 'flex-column')
					->content($content);

		if ($panel)
		{
			$nav = $this	->panel()
							->body($nav, FALSE);
		}

		return $nav;
	}
}
