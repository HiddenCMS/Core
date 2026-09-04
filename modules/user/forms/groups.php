<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

$this->js('group_appearance');

$colors = [
	'primary' => 'Turquoise',
	'secondary' => 'Gris',
	'success' => 'Vert',
	'danger' => 'Rose',
	'warning' => 'Jaune',
	'info' => 'Menthe',
	'light' => 'Clair',
	'dark' => 'Foncé'
];
$icons = [
	'' => 'Sans icône',
	'fas fa-user' => 'Utilisateur',
	'fas fa-users' => 'Groupe',
	'fas fa-user-tie' => 'Responsable',
	'fas fa-user-shield' => 'Modérateur',
	'fas fa-rocket' => 'Fusée',
	'fas fa-star' => 'Étoile',
	'fas fa-crown' => 'Couronne',
	'fas fa-shield-alt' => 'Bouclier',
	'fas fa-check-circle' => 'Validation',
	'fas fa-heart' => 'Cœur',
	'fas fa-gem' => 'Diamant',
	'fas fa-trophy' => 'Trophée',
	'fas fa-graduation-cap' => 'Diplôme',
	'fas fa-briefcase' => 'Travail',
	'fas fa-tools' => 'Outils',
	'fas fa-code' => 'Code',
	'fas fa-paint-brush' => 'Création',
	'fas fa-comments' => 'Discussion',
	'fas fa-globe' => 'Globe',
	'fas fa-lock' => 'Cadenas',
	'fas fa-eye' => 'Visibilité'
];

// Keep existing custom values available when editing a group.
$color = $this->form()->value('color');
$icon = $this->form()->value('icon');
if ($color && !isset($colors[$color])) $colors[$color] = 'Couleur actuelle';
if ($icon && !isset($icons[$icon])) $icons[$icon] = 'Icône actuelle';

$rules = [
	'title' => [
		'label' => $this->lang('Nom'),
		'value' => $this->form()->value('title'),
		'rules' => 'required'.($this->form()->value('auto') ? '|disabled' : '')
	],
	'color' => [
		'label' => $this->lang('Couleur'),
		'value' => $this->form()->value('color'),
		'default' => 'primary',
		'type'  => 'select',
		'values' => $colors,
		'check' => function($value) use ($colors){
			return is_string($value) && ($value === '' || isset($colors[$value])) ? TRUE : 'Couleur invalide';
		}
	],
	'icon' => [
		'label'   => $this->lang('Icône'),
		'value'   => $this->form()->value('icon'),
		'default' => 'fas fa-user',
		'type'    => 'select',
		'values'  => $icons,
		'check' => function($value) use ($icons){
			return is_string($value) && isset($icons[$value]) ? TRUE : 'Icône invalide';
		}
	],
	'hidden' => [
		'checked' => ['on' => $this->form()->value('hidden')],
		'values'  => ['on' => 'Groupe caché'],
		'type'    => 'checkbox'
	]
];
