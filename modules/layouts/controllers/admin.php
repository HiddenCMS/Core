<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Layouts\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Admin extends Controller_Module
{
	public function index($outlines)
	{
		$this	->table()
				->add_columns([
					[
						'title'   => $this->lang('Outline'),
						'content' => function($outline){
							return $outline['title'].'<small class="ml-2"><code>'.$outline['name'].'</code></small>';
						},
						'sort'    => function($outline){
							return $outline['title'];
						}
					],
					[
						'title'   => $this->lang('ThÃ¨me'),
						'content' => function($outline){
							return '<code>'.$outline['theme'].'</code>';
						},
						'sort'    => function($outline){
							return $outline['theme'];
						}
					],
					[
						'title'   => $this->lang('Base'),
						'content' => function($outline){
							return $outline['base'] ? icon('fas fa-check') : '';
						},
						'size'    => TRUE
					]
				])
				->data($outlines)
				->no_data($this->lang('Il n\'y a pas encore d\'outline'));

		return $this->panel()
					->heading($this->lang('Outlines'), 'fas fa-layer-group')
					->body($this->table()->display());
	}
}
