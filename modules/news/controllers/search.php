<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace NF\Modules\News\Controllers;

use NF\NeoFrag\Loadables\Controllers\Module as Controller_Module;

class Search extends Controller_Module
{
	public function index($result, $keywords)
	{
		$result['introduction'] = highlight($result['introduction']."\r\r".$result['content'], $keywords);
		return $this->view('search/index', $result);
	}

	public function detail($result, $keywords)
	{
		$result['introduction'] = highlight($result['introduction']."\r\r".$result['content'], $keywords, 1024);
		return $this->view('search/index', $result);
	}

	public function search()
	{
		$this->db	->select('n.news_id', 'n.date', 'nl.title', 'nl.introduction', 'nl.content', 'u.id as user_id', 'u.username', 'c.category_id', 'cl.title as category')
					->from('news n')
					->join('news_lang nl',            'n.news_id     = nl.news_id')
					->join('news_categories c',       'n.category_id = c.category_id')
					->join('news_categories_lang cl', 'c.category_id = cl.category_id')
					->join('user u',                  'n.user_id     = u.id AND u.deleted = "0"')
					->where('nl.lang', $this->config->lang->info()->name)
					->where('cl.lang', $this->config->lang->info()->name)
					->where('n.published', TRUE)
					->order_by('n.date DESC');

		return ['nl.title', 'nl.introduction', 'nl.content'];
	}
}
