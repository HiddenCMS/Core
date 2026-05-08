<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Widgets\Navigation\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Widget as Controller_Widget;

class Index extends Controller_Widget
{
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

		$nav = $this->html('ul')
					->attr('class', 'nav')
					->append_attr_if($type == 'vertical', 'class', 'flex-column');

		array_walk($settings['links'], $f = function(&$link) use (&$f){
			$link = array_merge([
				'title'  => '',
				'url'    => '',
				'icon'   => '',
				'access' => TRUE
			], $link);

			if (is_array($link['url']))
			{
				array_walk($link['url'], $f);
			}
		});

		$actives = [];

		$is_external = function($link){
			return strpos($link, '#') === 0 || strpos($link, 'mailto:') === 0 || preg_match('#^(?:https?:)?//#i', $link);
		};

		$link_href = function($link) use ($is_external){
			return $is_external($link) ? $link : url($link);
		};

		$is_active = function($link) use ($is_external){
			if ($is_external($link))
			{
				return FALSE;
			}

			return (($url = ltrim(preg_replace('_^'.preg_quote(url(), '_').'_', '', url($link)), '/')) == $this->url->request) || ($url && strpos($this->url->request, $url) === 0);
		};

		$nav_link = function($link, $active) use ($link_href){
			return $this->html('a')
						->attr('class', 'nav-link')
						->append_attr_if($active, 'class', 'active')
						->exec(function($a) use ($link){
							if (isset($link['modal']))
							{
								$this->js('modal');

								$a	->attr('href',            '#')
									->attr('data-modal-ajax', url($link['modal']));
							}
							else
							{
								$a->attr('href', !is_array($link['url']) ? $link_href($link['url']) : '#');

								if (!empty($link['target']))
								{
									$a->attr('target', $link['target']);
								}
							}
						})
						->content(icon($link['icon']).' '.$this->lang($link['title']));
		};

		$show_link = function($link, &$active = FALSE) use (&$actives, &$nav_link){
			if ($link['access'])
			{
				return $this->html('li')
							->attr('class', 'nav-item')
							->content($nav_link($link, $actives && $actives[0] == $link['url'] && ($active = TRUE)));
			}
		};

		foreach ($settings['links'] as $link)
		{
			if (is_array($link['url']))
			{
				foreach ($link['url'] as $link)
				{
					if ($is_active($link['url']))
					{
						$actives[] = $link['url'];
					}
				}
			}
			else if ($is_active($link['url']))
			{
				$actives[] = $link['url'];
			}
		}

		usort($actives, function($a, $b){
			return strlen($b) <=> strlen($a);
		});

		foreach ($settings['links'] as $link)
		{
			if (is_array($link['url']))
			{
				$active  = FALSE;
				$submenu = '';

				foreach ($link['url'] as $link2)
				{
					$submenu .= $show_link($link2, $active);
				}

				if ($submenu)
				{
					$nav->append($this	->html('li')
										->attr('class', 'nav-item')
										->content(
											$nav_link($link, $active)
												->attr('data-toggle', 'collapse')
												->attr('href',        '#')
												->content(icon($link['icon']).' <span class="hidden-xs">'.$this->lang($link['title']).'</span><span class="fas fa-angle-down"></span>').'<ul class="nav flex-column">'.$submenu.'</ul>'
										)
					);
				}
			}
			else
			{
				$nav->append($show_link($link));
			}
		}

		if ($panel)
		{
			$nav = $this	->panel()
							->body($nav, FALSE);
		}

		return $nav;
	}
}


