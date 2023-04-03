<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Modules\Pages\Controllers;

use HD\Hidden\Loadables\Controllers\Module as Controller_Module;

class Search extends Controller_Module
{
	public function index($result, $keywords)
	{
		$result['content'] = highlight($result['content'], $keywords);
		return $this->view('search/index', $result);
	}

	public function detail($result, $keywords)
	{
		$result['content'] = highlight($result['content'], $keywords, 1024);
		return $this->view('search/index', $result);
	}

	public function search()
	{
		$this->db	->select('p.page_id', 'p.name', 'pl.title', 'pl.subtitle', 'pl.content')
					->from('pages p')
					->join('pages_lang pl', 'p.page_id = pl.page_id')
					->where('p.published', TRUE)
					->where('pl.lang', $this->config->lang->info()->name);

		return ['p.page_id', 'p.name', 'pl.title', 'pl.subtitle', 'pl.content'];
	}
}
