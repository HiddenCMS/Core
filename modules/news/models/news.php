<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace NF\Modules\News\Models;

use NF\NeoFrag\Loadables\Model;

class News extends Model
{
	public function get_news($filter = '', $filter_data = '')
	{
		$this->db	->select('n.*', 'nl.title', 'nl.introduction', 'nl.content', 'nl.tags', 'IFNULL(n.image_id, c.image_id) as image', 'c.icon_id as category_icon', 'c.name as category_name', 'cl.title as category_title', 'u.id as user_id', 'u.username', 'up.avatar', 'up.sex')
					->from('news n')
					->join('news_lang nl',            'n.news_id     = nl.news_id')
					->join('news_categories c',       'n.category_id = c.category_id')
					->join('news_categories_lang cl', 'c.category_id = cl.category_id')
					->join('user u',                  'n.user_id     = u.id AND u.deleted = "0"')
					->join('user_profile up',         'up.id         = u.id')
					->where('nl.lang', $this->config->lang->info()->name)
					->where('cl.lang', $this->config->lang->info()->name)
					->order_by('n.date DESC');

		if (!empty($filter) && !empty($filter_data))
		{
			if ($filter == 'tag')
			{
				$this->db->where('nl.tags FIND_IN_SET', $filter_data);
			}
			else if ($filter == 'category')
			{
				$this->db->where('n.category_id', $filter_data);
			}
		}

		if (!$this->url->admin)
		{
			$this->db->where('n.published', TRUE);
		}

		return $this->db->get();
	}

	public function get_news_by_user($user_id, $news_id)
	{
		return $this->db->select('n.news_id', 'nl.title', 'cl.title as category_title')
						->from('news n')
						->join('news_lang nl',            'n.news_id     = nl.news_id')
						->join('news_categories c',       'n.category_id = c.category_id')
						->join('news_categories_lang cl', 'c.category_id = cl.category_id')
						->where('n.published', TRUE)
						->where('nl.lang', $this->config->lang->info()->name)
						->where('cl.lang', $this->config->lang->info()->name)
						->where('n.user_id', $user_id)
						->where('n.news_id <>', $news_id)
						->order_by('n.date DESC')
						->limit(5)
						->get();
	}

	public function check_news($news_id, $title, $lang = 'default')
	{
		if ($lang == 'default')
		{
			$lang = $this->config->lang->info()->name;
		}

		$this->db	->select('n.news_id', 'n.category_id', 'u.id as user_id', 'n.image_id', 'n.date', 'n.published', 'n.views', 'n.vote', 'nl.title', 'nl.introduction', 'nl.content', 'nl.tags', 'c.name as category_name', 'cl.title as category_title', 'IFNULL(n.image_id, c.image_id) as image', 'c.icon_id as category_icon', 'u.username', 'u.admin', 'MAX(s.last_activity) > DATE_SUB(NOW(), INTERVAL 5 MINUTE) as online', 'up.quote', 'up.avatar', 'up.sex')
						->from('news n')
						->join('news_lang nl',            'n.news_id     = nl.news_id')
						->join('news_categories c',       'n.category_id = c.category_id')
						->join('news_categories_lang cl', 'c.category_id = cl.category_id')
						->join('user u',                  'u.id          = n.user_id AND u.deleted = "0"')
						->join('user_profile up',         'u.id          = up.id')
						->join('session        s',        'u.id          = s.user_id')
						->where('n.news_id', $news_id)
						->where('nl.lang', $lang)
						->where('cl.lang', $lang);

		if (!$this->url->admin)
		{
			$this->db->where('n.published', TRUE);
		}

		$news = $this->db->row();

		if ($news && url_title($news['title']) == $title)
		{
			return $news;
		}
		else
		{
			return FALSE;
		}
	}

	public function add_news($title, $category_id, $image_id, $introduction, $content, $tags, $published)
	{
		$news_id = $this->db->insert('news', [
								'category_id' => $category_id,
								'user_id'     => $this->user->id,
								'image_id'    => $image_id,
								'published'   => $published
							]);

		$this->db	->insert('news_lang', [
						'news_id'      => $news_id,
						'lang'         => $this->config->lang->info()->name,
						'title'        => $title,
						'introduction' => $introduction,
						'content'      => $content,
						'tags'         => $this->_tags($tags)
					]);
	}

	public function edit_news($news_id, $category_id, $image_id, $published, $title, $introduction, $content, $tags, $lang)
	{
		$this->db	->where('news_id', $news_id)
					->update('news', [
						'category_id' => $category_id,
						'image_id'    => $image_id,
						'published'   => $published
					]);

		$this->db	->where('news_id', $news_id)
					->where('lang', $lang)
					->update('news_lang', [
						'title'        => $title,
						'introduction' => $introduction,
						'content'      => $content,
						'tags'         => $this->_tags($tags)
					]);
	}

	public function delete_news($news_id)
	{
		NeoFrag()->model2('file', $this->db->select('image_id')->from('news')->where('news_id', $news_id)->row())->delete();

		if ($comments = $this->module('comments'))
		{
			$comments->delete('news', $news_id);
		}

		$this->db	->where('news_id', $news_id)
					->delete('news');
	}

	private function _tags($tags)
	{
		return implode(',', array_unique(array_map('trim', preg_split('/[ ,;]+/', $tags, -1, PREG_SPLIT_NO_EMPTY))));
	}
}
