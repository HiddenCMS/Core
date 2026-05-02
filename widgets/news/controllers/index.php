<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Widgets\News\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Widget as Controller_Widget;

class Index extends Controller_Widget
{
	public function index($config = [])
	{
		$this->css('news');

		$news = array_filter($this->module('news')->model()->get_news(), function($a){
			return $a['published'];
		});

		if (!empty($news))
		{
			return $this->panel()
						->heading($this->lang('ActualitÃ©s rÃ©centes'))
						->body($this->view('index', [
							'news' => array_slice($news, 0, 3)
						]))
						->footer('<a href="'.url('news').'">'.icon('far fa-arrow-alt-circle-right').' '.$this->lang('Voir toutes les actualitÃ©s').'</a>', 'right');
		}
		else
		{
			return $this->panel()
						->heading($this->lang('ActualitÃ©s rÃ©centes'))
						->body($this->lang('Aucune actualitÃ© pour le moment'));
		}
	}

	public function categories($config = [])
	{
		$categories = $this->module('news')->model('categories')->get_categories();

		if (!empty($categories))
		{
			return $this->panel()
						->heading($this->lang('CatÃ©gories'))
						->body($this->view('categories', [
							'categories' => $categories
						]), FALSE);
		}
		else
		{
			return $this->panel()
						->heading($this->lang('CatÃ©gories'))
						->body($this->lang('Aucune catÃ©gorie pour le moment'));
		}
	}

	public function tags($config = [])
	{
		$unique_tags = [];

		if ($tags = $this->db	->select('nl.tags')
								->from('news_lang nl')
								->join('news n', 'nl.news_id = n.news_id')
								->where('n.published', TRUE)
								->get())
		{
			foreach ($tags as $tag)
			{
				$unique_tags = array_merge($unique_tags, explode(',', $tag));
			}
		}

		$unique_tags = array_unique($unique_tags);

		if (!empty($unique_tags))
		{
			return $this->panel()
						->heading($this->lang('Tags'))
						->body($this->view('tags', [
							'tags' => $unique_tags
						]));
		}
		else
		{
			return $this->panel()
						->heading($this->lang('Tags'))
						->body($this->lang('Aucun tag pour le moment'));
		}
	}
}


