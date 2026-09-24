<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Pages\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Admin extends Controller_Module
{
	public function index($pages)
	{
		$this->css('pages');

		return $this->panel()
					->heading($this->lang('Pages list'), 'fas fa-bars')
					->body($this->render_pages_tree($pages), FALSE)
					->footer_if($this->is_authorized('add_pages'), $this->button_create('admin/pages/add', $this->lang('Create a page')));
	}

	private function render_pages_tree($pages)
	{
		if (empty($pages))
		{
			return '<div class="table-empty">'.$this->lang('There are no pages').'</div>';
		}

		$children = [];

		foreach ($pages as $page)
		{
			$parent_id = !empty($page['parent_id']) ? (int)$page['parent_id'] : 0;
			$children[$parent_id][] = $page;
		}

		$render = function($parent_id = 0, $level = 0) use (&$render, $children){
			if (empty($children[$parent_id]))
			{
				return '';
			}

			$html = '<ul class="pages-tree '.(!$level ? 'pages-tree-root' : 'pages-tree-children').'">';

			foreach ($children[$parent_id] as $page)
			{
				$title = utf8_htmlentities(utf8_html_entity_decode($page['title'], ENT_QUOTES), ENT_QUOTES);
				$subtitle = trim(utf8_html_entity_decode((string)$page['subtitle'], ENT_QUOTES));
				$actions = [];

				if ($page['published'])
				{
					$actions[] = $this->button()
									->tooltip($this->lang('View page'))
									->icon('far fa-eye')
									->url($page['path'])
									->color('secondary')
									->compact()
									->outline();
				}

				if ($this->user->admin)
				{
					$actions[] = $this->button_access($page['page_id'], 'page');
				}

				if ($this->is_authorized('modify_pages'))
				{
					$actions[] = $this->button_update('admin/pages/'.$page['page_id'].'/'.url_title($page['title']));
				}

				if ($this->is_authorized('delete_pages'))
				{
					$actions[] = $this->button_delete('admin/pages/delete/'.$page['page_id'].'/'.url_title($page['title']));
				}

				$status_title = $page['published'] ? $this->lang('Published') : $this->lang('Pending publication');
				$status = '<i class="pages-tree-status '.($page['published'] ? 'fas fa-circle is-published' : 'far fa-circle is-draft').'" data-toggle="tooltip" title="'.$status_title.'" aria-label="'.$status_title.'"></i>';
				$edit_url = 'admin/pages/'.$page['page_id'].'/'.url_title($page['title']);
				$title_output = $this->is_authorized('modify_pages')
					? '<a class="pages-tree-title" href="'.url($edit_url).'">'.$title.'</a>'
					: '<span class="pages-tree-title">'.$title.'</span>';

				$html .= '<li class="pages-tree-item">'
						.'<div class="pages-tree-row">'
							.'<div class="pages-tree-main">'
								.$status
								.'<div class="pages-tree-copy">'
									.'<div class="pages-tree-heading">'.$title_output.'<code class="ui tiny label pages-tree-path">/'.utf8_htmlentities($page['path']).'</code></div>'
									.($subtitle !== '' ? '<small class="pages-tree-subtitle">'.utf8_htmlentities($subtitle).'</small>' : '')
								.'</div>'
							.'</div>'
							.'<div class="pages-tree-actions">'.implode('', array_filter($actions)).'</div>'
						.'</div>'
						.$render((int)$page['page_id'], $level + 1)
					.'</li>';
			}

			return $html.'</ul>';
		};

		return $render();
	}

	public function add()
	{
		$this->css('pages');

		$outlines = $this->model()->get_outlines();

		$this	->subtitle($this->lang('Add page'))
				->form()
				->add_rules('pages', [
					'page_id'         => 0,
					'parent_id'       => 0,
					'parents'         => $this->model()->get_parent_choices(),
					'modules'         => $this->model()->get_page_modules(),
					'outline_id'      => key($outlines),
					'outlines'        => $outlines,
					'blocks'          => $this->storage->encode([])
				])
				->add_submit($this->lang('Add'))
				->add_back('admin/pages');

		if ($this->form()->is_valid($post))
		{
			$page_name = $this->model()->normalize_page_name($post['name'], $post['title']);

			$parent_id = (int)$post['parent_id'];

			if (!$parent_id && $this->model()->is_reserved_page_name($page_name))
			{
				notify($this->lang('The first path segment is reserved by a module'), 'danger');
			}
			else
			{
			$blocks = $this->model()->build_blocks($post);

			$this->model()->add_page(	$page_name,
										$post['title'],
										in_array('on', $post['published']),
										!empty($post['outline_id']) ? $post['outline_id'] : NULL,
										$post['subtitle'],
										'',
										$blocks,
										$parent_id);

			notify($this->lang('Page successfully added'));

			redirect_back('admin/pages');
			}
		}

		return $this->panel()
					->heading($this->lang('Add page'), 'fas fa-align-left')
					->body($this->form()->display());
	}

	public function _edit($page_id, $name, $published, $outline_id, $parent_id, $title, $subtitle, $content, $tab)
	{
		$this->css('pages');
		$form_title = utf8_html_entity_decode($title, ENT_QUOTES);
		$form_subtitle = utf8_html_entity_decode($subtitle, ENT_QUOTES);

		$this	->subtitle($form_title)
				->form()
				->add_rules('pages', [
					'page_id'        => $page_id,
					'parent_id'      => $parent_id,
					'parents'        => $this->model()->get_parent_choices($page_id),
					'title'          => $form_title,
					'subtitle'       => $form_subtitle,
					'name'           => $name,
					'published'      => $published,
					'outline_id'     => $outline_id,
					'modules'        => $this->model()->get_page_modules(),
					'outlines'       => $this->model()->get_outlines(),
					'blocks'         => $this->model()->get_blocks_form_value($page_id, $content)
				])
				->add_submit($this->lang('Edit'))
				->add_back('admin/pages');

		if ($this->form()->is_valid($post))
		{
			$page_name = $this->model()->normalize_page_name($post['name'], $post['title']);
			$parent_id = (int)$post['parent_id'];

			if (!$parent_id && $this->model()->is_reserved_page_name($page_name))
			{
				notify($this->lang('The root page slug is reserved by a module'), 'danger');
				return $this->panel()->heading($this->lang('Edit page'), 'fas fa-align-left')->body($this->form()->display());
			}

			$blocks = $this->model()->build_blocks($post);

			$this->model()->edit_page(	$page_id,
										$page_name,
										$post['title'],
										in_array('on', $post['published']),
										!empty($post['outline_id']) ? $post['outline_id'] : NULL,
										$post['subtitle'],
										'',
										$this->config->lang->info()->name,
										$blocks,
										$parent_id);

			notify($this->lang('Page successfully edited'));

			redirect_back('admin/pages');
		}

		return $this->panel()
					->heading($this->lang('Edit page'), 'fas fa-align-left')
					->body($this->form()->display());
	}

	public function delete($page_id, $title)
	{
		if ($this->model()->has_children($page_id))
		{
			return '<div class="ui warning message"><div class="header">'.$this->lang('This page has child pages').'</div><p>'.$this->lang('Move or delete its child pages before deleting this page.').'</p></div>';
		}

		$this	->title($this->lang('Delete page'))
				->subtitle($title)
				->form()
				->confirm_deletion($this->lang('Confirm deletion'), $this->lang('Are you sure you want to delete page <b>%s</b>?', $title));

		if ($this->form()->is_valid())
		{
			$this->model()->delete_page($page_id);

			return 'OK';
		}

		return $this->form()->display();
	}
}


