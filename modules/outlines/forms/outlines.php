<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

$name = $this->form()->value('name');

$rules = [
	'title' => [
		'label' => $this->lang('Title'),
		'value' => $this->form()->value('title'),
		'type'  => 'text',
		'rules' => 'required'
	],
	'name' => [
		'label' => $this->lang('Internal name'),
		'value' => $name,
		'type'  => 'text',
		'check' => function($value, $post) use ($name){
			if (!$value)
			{
				$value = $post['title'];
			}

			$value = url_title($value);

			if ($value != $name && !HiddenCMS()->db->from('outlines')->where('name', $value)->empty())
			{
				return $this->lang('Internal name already in use');
			}
		}
	],
	'theme' => [
		'label'  => $this->lang('Theme'),
		'value'  => $this->form()->value('theme'),
		'values' => $this->form()->value('themes'),
		'type'   => 'select',
		'rules'  => 'required'
	],
	'base' => [
		'type'    => 'checkbox',
		'checked' => ['on' => $this->form()->value('base')],
		'values'  => ['on' => $this->lang('Default outline')]
	],
	'enabled' => [
		'type'    => 'checkbox',
		'checked' => ['on' => $this->form()->value('enabled')],
		'values'  => ['on' => $this->lang('Enable this outline')]
	]
];
