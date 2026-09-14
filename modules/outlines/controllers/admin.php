<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Outlines\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Admin extends Controller_Module
{
	private function outline_model()
	{
		return $this->module->model2('outline');
	}

	private function checkbox_enabled($values)
	{
		return is_array($values) && in_array('on', $values, TRUE);
	}

	private function component($name, array $data, $fallback = '')
	{
		$template = HB()->template('outlines/'.$name, $data, $fallback);

		if ($theme = $this->output->theme())
		{
			$template->owner($theme);
		}

		return (string)$template->owner($this->module);
	}

	public function index($outlines)
	{
		$actions = function($outline){
			$buttons = [
				(string)$this->button_update('admin/live-editor?outline_id='.$outline['outline_id'], $this->lang('Edit visually'))
							->icon('fas fa-desktop'),
				(string)$this->button_duplicate_modal($this->_duplicate($outline)),
				(string)$this->button_update('admin/outlines/'.$outline['outline_id'].'/'.url_title($outline['title']))
			];

			if (!$outline['base'])
			{
				$buttons[] = (string)$this->button()
										->tooltip($this->lang('Delete'))
										->icon('far fa-trash-alt')
										->color('danger')
										->compact()
										->modal($this->_delete($outline));
			}

			$buttons = array_filter($buttons);

			return $this->component('actions', [
				'buttons' => $buttons
			], implode('', $buttons));
		};

		$table = $this	->table2($this->array($outlines), $this->lang('There are no outlines yet'))
						->col($this->lang('Outline'), function($outline){
							return $this->component('identity', [
								'title' => $outline['title'],
								'name'  => $outline['name']
							], utf8_htmlentities($outline['title']).' <code>'.utf8_htmlentities($outline['name']).'</code>');
						})
						->col($this->lang('Theme'), function($outline){
							return $this->component('theme', [
								'theme' => $outline['theme']
							], '<code>'.utf8_htmlentities($outline['theme']).'</code>');
						})
						->col($this->lang('Base'), 'compact', 'center', function($outline){
							return $outline['base'] ? icon('fas fa-check') : '';
						})
						->col($this	->table_col()
									->title($this->lang('Actions'))
									->align('right')
									->style('text-nowrap')
									->content($actions)
						);

		return $table->panel()
					->title($this->lang('Outlines'), 'fas fa-layer-group')
					->footer($this->button_create('admin/outlines/add', $this->lang('Add an outline')));
	}

	public function add()
	{
		$this->subtitle($this->lang('Add an outline'));

		return $this	->form2('outline', [
						'outline_id' => 0,
						'theme'      => $this->config->default_theme,
						'themes'     => $this->outline_model()->get_themes(),
						'reserved_routes' => $this->outline_model()->get_reserved_route_choices(),
						'selected_reserved_routes' => [],
						'breadcrumb' => TRUE,
						'enabled'    => TRUE
					])
					->success(function($data, $form){
						if (!($outline_id = $this->outline_model()->add_outline(
							trim((string)($data['name'] ?? '')),
							trim((string)($data['title'] ?? '')),
							trim((string)($data['theme'] ?? '')),
							$this->checkbox_enabled($data['base'] ?? []),
							$this->checkbox_enabled($data['breadcrumb'] ?? []),
							$this->checkbox_enabled($data['enabled'] ?? [])
						)))
						{
							$form->error($this->lang('Unable to save the outline'));
							return;
						}

						$this->outline_model()->set_reserved_routes($outline_id, $data['reserved_routes'] ?? []);

						notify($this->lang('Outline added successfully'));
						redirect('admin/live-editor?outline_id='.$outline_id);
					})
					->submit($this->lang('Add'))
					->back('admin/outlines')
					->panel()
					->heading($this->lang('Add an outline'), 'fas fa-layer-group');
	}

	public function _edit($outline_id, $name, $title, $theme, $base, $breadcrumb, $enabled)
	{
		$this->subtitle($title);

		return $this	->form2('outline', [
						'outline_id' => (int)$outline_id,
						'name'       => $name,
						'title'      => $title,
						'theme'      => $theme,
						'themes'     => $this->outline_model()->get_themes(),
						'reserved_routes' => $this->outline_model()->get_reserved_route_choices(),
						'selected_reserved_routes' => $this->outline_model()->get_reserved_routes($outline_id),
						'base'       => (bool)$base,
						'breadcrumb' => (bool)$breadcrumb,
						'enabled'    => (bool)$enabled
					])
					->success(function($data, $form) use ($outline_id){
						$result = $this->outline_model()->edit_outline(
							(int)$outline_id,
							trim((string)($data['name'] ?? '')),
							trim((string)($data['title'] ?? '')),
							trim((string)($data['theme'] ?? '')),
							$this->checkbox_enabled($data['base'] ?? []),
							$this->checkbox_enabled($data['breadcrumb'] ?? []),
							$this->checkbox_enabled($data['enabled'] ?? [])
						);

						if (!$result)
						{
							$form->error($this->lang('Unable to save the outline'));
							return;
						}

						$this->outline_model()->set_reserved_routes($outline_id, $data['reserved_routes'] ?? []);

						notify($this->lang('Outline updated successfully'));
						redirect_back('admin/outlines');
					})
					->submit($this->lang('Edit'))
					->back('admin/outlines')
					->panel()
					->heading($this->lang('Edit outline'), 'fas fa-layer-group')
					->footer(
						$this->button()
							->title($this->lang('Edit visually'))
							->icon('fas fa-desktop')
							->color('info')
							->url('admin/live-editor?outline_id='.$outline_id)
					);
	}

	public function _duplicate($outline)
	{
		return $this	->form2()
					->rule($this	->form_text('title')
									->title($this->lang('New outline name'))
									->value($this->lang('Copy of %s', $outline['title']))
									->required()
									->check(function($post){
										$name = !empty($post['title']) ? url_title($post['title']) : '';

										if ($name && $this->outline_model()->name_exists($name))
										{
											return $this->lang('An outline already uses this name');
										}
									})
					)
					->success(function($data) use ($outline){
						if (!($outline_id = $this->outline_model()->duplicate_outline($outline['outline_id'], $data['title'])))
						{
							notify($this->lang('Unable to duplicate the outline'), 'danger');
							refresh();
						}

						notify($this->lang('Outline duplicated successfully'));

						redirect('admin/live-editor?outline_id='.$outline_id);
					})
					->submit($this->lang('Duplicate'))
					->modal($this->lang('Duplicate %s', $outline['title']), 'far fa-copy')
					->cancel();
	}

	public function _delete($outline)
	{
		return $this	->modal($this->lang('Delete %s', $outline['title']), 'far fa-trash-alt text-danger')
					->body($this->lang('Pages using this outline will be assigned to the default outline.'))
					->callback(function() use ($outline){
						if (!$this->outline_model()->delete_outline($outline['outline_id']))
						{
							notify($this->lang('Unable to delete the outline'), 'danger');
							refresh();
						}

						notify($this->lang('Outline deleted successfully'));

						redirect('admin/outlines');
					})
					->submit($this->lang('Delete'), 'danger')
					->cancel();
	}
}
