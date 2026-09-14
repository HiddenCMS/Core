<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

$this->js('group_appearance');

$colors = [
	'primary' => 'Turquoise',
	'secondary' => (string)$this->lang('Gray'),
	'success' => (string)$this->lang('Green'),
	'danger' => (string)$this->lang('Pink'),
	'warning' => (string)$this->lang('Yellow'),
	'info' => (string)$this->lang('Mint'),
	'light' => (string)$this->lang('Light'),
	'dark' => (string)$this->lang('Dark')
];
$icons = [
	'' => (string)$this->lang('No icon'),
	'fas fa-user' => (string)$this->lang('User'),
	'fas fa-users' => (string)$this->lang('Group'),
	'fas fa-user-tie' => (string)$this->lang('Manager'),
	'fas fa-user-shield' => (string)$this->lang('Moderator'),
	'fas fa-rocket' => (string)$this->lang('Rocket'),
	'fas fa-star' => (string)$this->lang('Star'),
	'fas fa-crown' => (string)$this->lang('Crown'),
	'fas fa-shield-alt' => (string)$this->lang('Shield'),
	'fas fa-check-circle' => (string)$this->lang('Approval'),
	'fas fa-heart' => (string)$this->lang('Heart'),
	'fas fa-gem' => (string)$this->lang('Diamond'),
	'fas fa-trophy' => (string)$this->lang('Trophy'),
	'fas fa-graduation-cap' => (string)$this->lang('Graduation'),
	'fas fa-briefcase' => (string)$this->lang('Work'),
	'fas fa-tools' => (string)$this->lang('Tools'),
	'fas fa-code' => 'Code',
	'fas fa-paint-brush' => (string)$this->lang('Creative'),
	'fas fa-comments' => 'Discussion',
	'fas fa-globe' => 'Globe',
	'fas fa-lock' => (string)$this->lang('Padlock'),
	'fas fa-eye' => (string)$this->lang('Visibility')
];

// Keep existing custom values available when editing a group.
$color = $this->form()->value('color');
$icon = $this->form()->value('icon');
if ($color && !isset($colors[$color])) $colors[$color] = (string)$this->lang('Current color');
if ($icon && !isset($icons[$icon])) $icons[$icon] = (string)$this->lang('Current icon');

$rules = [
	'title' => [
		'label' => $this->lang('Name'),
		'value' => $this->form()->value('title'),
		'rules' => 'required'.($this->form()->value('auto') ? '|disabled' : '')
	],
	'color' => [
		'label' => $this->lang('Color'),
		'value' => $this->form()->value('color'),
		'default' => 'primary',
		'type'  => 'select',
		'values' => $colors,
		'check' => function($value) use ($colors){
			return is_string($value) && ($value === '' || isset($colors[$value])) ? TRUE : (string)$this->lang('Invalid color');
		}
	],
	'icon' => [
		'label'   => $this->lang('Icon'),
		'value'   => $this->form()->value('icon'),
		'default' => 'fas fa-user',
		'type'    => 'select',
		'values'  => $icons,
		'check' => function($value) use ($icons){
			return is_string($value) && isset($icons[$value]) ? TRUE : (string)$this->lang('Invalid icon');
		}
	],
	'hidden' => [
		'checked' => ['on' => $this->form()->value('hidden')],
		'values'  => ['on' => (string)$this->lang('Hidden group')],
		'type'    => 'checkbox'
	]
];
