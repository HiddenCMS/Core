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

			if ($value != $name && !HiddenCMS()->db->from('pages')->where('name', $value)->empty())
			{
				return $this->lang('Chemin d\'accès déjà utilisé');
			}
		}
	]
];

if ($modules = $this->form()->value('modules'))
{
	$rules += [
		'module' => [
			'label'  => $this->lang('Type de contenu'),
			'value'  => $this->form()->value('module'),
			'values' => $modules,
			'type'   => 'select'
		],
		'news_category' => [
			'label'  => $this->lang('Catégorie d\'actualités'),
			'value'  => $this->form()->value('news_category'),
			'values' => $this->form()->value('news_categories'),
			'type'   => 'select'
		]
	];
}

$rules += [
	'content' => [
		'label' => $this->lang('Contenu'),
		'value' => $this->form()->value('content'),
		'type'  => 'editor'
	],
	'published' => [
		'type'    => 'checkbox',
		'checked' => ['on' => $this->form()->value('published')],
		'values'  => ['on' => $this->lang('Publier la page dès maintenant')]
	]
];

if ($modules)
{
	$this->js_load('
		(function(){
			var updatePageForm = function(){
				var module = $("[name$=\"[module]\"]").val();
				$("[name$=\"[content]\"]").closest(".form-group").toggle(!module);
				$("[name$=\"[news_category]\"]").closest(".form-group").toggle(module == "news");
			};

			$("[name$=\"[module]\"]").on("change", updatePageForm);
			updatePageForm();
		})();
	');
}
