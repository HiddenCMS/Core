<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

$rules = [
	'title' => [
		'label'         => $this->lang('Titre de la page'),
		'value'         => $this->form()->value('title'),
		'type'          => 'text',
		'rules'         => 'required'
	],
	'subtitle' => [
		'label'         => $this->lang('Sous-titre'),
		'value'         => $this->form()->value('subtitle'),
		'type'          => 'text'
	],
	'name' => [
		'label'         => $this->lang('Chemin d\'accès'),
		'value'         => $name = $this->form()->value('name'),
		'type'          => 'text',
		'check'         => function($value, $post) use ($name){
			if (!$value)
			{
				$value = $post['title'];
			}

			$value = url_title($value);

			if ($value != $name && !NeoFrag()->db->from('pages')->where('name', $value)->empty())
			{
				return $this->lang('Chemin d\'accès déjà utilisé');
			}
		}
	],
	'content' => [
		'label'			=> $this->lang('Contenu'),
		'value'			=> $this->form()->value('content'),
		'type'			=> 'editor'
	]
];

if ($modules = $this->form()->value('modules'))
{
	$rules += [
		[
			'label' => $this->lang('Instance de module'),
			'type'  => 'legend'
		],
		'module' => [
			'label'  => $this->lang('Module principal'),
			'value'  => $this->form()->value('module'),
			'values' => $modules,
			'type'   => 'select'
		],
		'module_route' => [
			'label'       => $this->lang('Route interne'),
			'value'       => $this->form()->value('module_route'),
			'type'        => 'text',
			'description' => $this->lang('Exemple: category/2/esport pour afficher une catÃ©gorie d\'actualitÃ©s')
		]
	];
}

$rules += [
	'published' => [
		'type'			=> 'checkbox',
		'checked'		=> ['on' => $this->form()->value('published')],
		'values'        => ['on' => $this->lang('Publier la page dès maintenant')]
	]
];
