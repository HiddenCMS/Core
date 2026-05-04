<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\News\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module_Checker;

class Checker extends Module_Checker
{
	public function index($page = '')
	{
		return [$this->module->pagination->fix_items_per_page($this->config->news_per_page)->get_data($this->model()->get_news(), $page)];
	}

	public function _tag($tag, $page = '')
	{
		return [$tag, $this->module->pagination->fix_items_per_page($this->config->news_per_page)->get_data($this->model()->get_news('tag', $tag), $page)];
	}

	public function _category($name, $page = '')
	{
		if ($category = $this->model('categories')->check_category_by_name($name))
		{
			return [$category['title'], $this->module->pagination->fix_items_per_page($this->config->news_per_page)->get_data($this->model()->get_news('category', $category['category_id']), $page)];
		}
	}

	public function _news($category_name, $title)
	{
		if ($news = $this->model()->check_news_by_slugs($category_name, $title))
		{
			return $news;
		}
	}
}


