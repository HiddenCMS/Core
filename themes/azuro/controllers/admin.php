<?php
/**
 * https://neofr.ag
 * @author: Jérémy VALENTIN <jeremy.valentin@neofr.ag>
 */

namespace HB\Themes\Azuro\Controllers;

use HB\HiddenCMS\Loadables\Controller;

class Admin extends Controller
{
	public function index()
	{
		$this	->css('admin')
				->js('admin');

		$form_background = $this->form()
								->add_rules([
									'background' => [
										'label'  => $this->lang('Background image'),
										'value'  => $this->config->{'azuro_background'},
										'type'   => 'file',
										'upload' => 'themes/azuro/backgrounds',
										'info'   => $this->lang('image (max. %d MB)', file_upload_max_size() / 1024 / 1024),
										'check'  => function($filename, $ext){
											if (!in_array($ext, ['gif', 'jpeg', 'jpg', 'png']))
											{
											return $this->lang('Please choose an image file');
											}
										}
									],
									'repeat' => [
										'label'  => $this->lang('Repeat image'),
										'value'  => $this->config->{'azuro_background_repeat'},
										'values' => [
											'no-repeat' => $this->lang('No'),
											'repeat-x'  => $this->lang('Horizontally'),
											'repeat-y'  => $this->lang('Vertically'),
											'repeat'    => $this->lang('Both')
										],
										'type'   => 'radio',
										'rules'  => 'required'
									],
									'positionX' => [
										'label'  => $this->lang('Position'),
										'value'  => explode(' ', $this->config->{'azuro_background_position'})[0],
										'values' => [
											'left'   => $this->lang('Left'),
											'center' => $this->lang('Centered'),
											'right'  => $this->lang('Right')
										],
										'type'   => 'radio',
										'rules'  => 'required'
									],
									'positionY' => [
										'value'  => explode(' ', $this->config->{'azuro_background_position'})[1],
										'values' => [
											'top'    => $this->lang('Top'),
											'center' => $this->lang('Medium'),
											'bottom' => $this->lang('Bottom')
										],
										'type'   => 'radio',
										'rules'  => 'required'
									],
									'fixed' => [
										'checked' => ['on' => $this->config->{'azuro_background_attachment'} == 'fixed'],
										'values'  => ['on' => $this->lang('Fixed image')],
										'type'    => 'checkbox'
									],
									'color' => [
										'label' => $this->lang('Background color'),
										'value' => $this->config->{'azuro_background_color'},
										'type'  => 'colorpicker',
										'rules' => 'required',
										'size'  => 'col-3'
									]
								])
								->add_submit($this->lang('Save'))
								->save();

		$form_colors = $this->form()
							->add_rules([
								'primary' => [
									'label' => $this->lang('Primary color'),
									'value' => $this->config->{'azuro_primary_color'},
									'type'  => 'colorpicker',
									'rules' => 'required',
									'size'  => 'col-3'
								],
								'secondary' => [
									'label' => $this->lang('Secondary color'),
									'value' => $this->config->{'azuro_secondary_color'},
									'type'  => 'colorpicker',
									'rules' => 'required',
									'size'  => 'col-3'
								],
								'text' => [
									'label' => $this->lang('Text color'),
									'value' => $this->config->{'azuro_text_color'},
									'type'  => 'colorpicker',
									'rules' => 'required',
									'size'  => 'col-3'
								]
							])
							->add_submit($this->lang('Save'))
							->save();

		if ($form_background->is_valid($post))
		{
			if ($post['background'])
			{
				$this->config('azuro_background', $post['background'], 'int');
			}
			else
			{
				$this->config->unset('azuro_background');
			}

			$this	->config('azuro_background_repeat',     $post['repeat'])
					->config('azuro_background_attachment', in_array('on', $post['fixed']) ? 'fixed' : 'scroll')
					->config('azuro_background_position',   $post['positionX'].' '.$post['positionY'])
					->config('azuro_background_color',      $post['color']);

			$this->module('tools')->api()->scss();

			notify($this->lang('Header background updated!'));

			redirect($this->url->location.'#background');
		}
		else if ($form_colors->is_valid($post))
		{
			$this	->config('azuro_primary_color',   $post['primary'])
					->config('azuro_secondary_color', $post['secondary'])
					->config('azuro_text_color',      $post['text']);

			$this->module('tools')->api()->scss();

			notify($this->lang('Theme colors updated!'));

			redirect($this->url->location.'#colors');
		}

		return $this->row(
			$this	->col(
						$this	->panel()
								->style('theme-customizer-shell')
								->body($this->view('admin/index', [
									'theme'           => $this->__caller->info(),
									'form_background' => $form_background->display(),
									'form_colors'     => $form_colors->display()
								]), FALSE)
					)
					->size('col-12')
		);
	}
}


