<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

$name = $this->form()->value('name');

$rules = [
	'title' => [
		'label' => $this->lang('Titre'),
		'value' => $this->form()->value('title'),
		'type'  => 'text',
		'rules' => 'required'
	],
	'name' => [
		'label' => $this->lang('Nom technique'),
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
				return $this->lang('Nom technique deja utilise');
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
		'values'  => ['on' => $this->lang('Outline de base')]
	],
	'enabled' => [
		'type'    => 'checkbox',
		'checked' => ['on' => $this->form()->value('enabled')],
		'values'  => ['on' => $this->lang('Activer cet outline')]
	]
];
