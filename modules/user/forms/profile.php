<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

$rules = [
		$this->form_text('first_name')
					->title('Prénom')
					->size('col-6'),
		$this->form_text('last_name')
					->title('Nom')
					->size('col-6'),
		$this->form_date('date_of_birth')
					->title('Date de naissance')
					->check(function($post, $data){
						if (!is_empty($data['date_of_birth']) && $this->date($data['date_of_birth'])->diff() > 0)
						{
							return 'Date de naissance invalide';
						}
					})
					->size('col-6'),
		$this->form_select('sex')
					->title('Sexe')
					->data([
						'unspecified' => 'Ne souhaite pas préciser',
						'female' => 'Femme',
						'male'   => 'Homme'
					])
					->value($this->model()->sex ?: 'unspecified')
					->check(function($post){
						if (!in_array($post['sex'] ?? '', ['unspecified', 'female', 'male'], TRUE))
						{
							return 'Choix invalide';
						}
					})
					->size('col-6'),
		$this->form_select('country')
					->title('Pays')
					->placeholder('Non renseigné')
					->data(get_countries())
					->size('col-6'),
		$this->form_text('location')
					->title('Localisation')
					->size('col-6'),
		$this->form_text('quote')
					->title('Citation'),
		$this->form_bbcode('signature')
					->title('Signature')
					->rows(5)
];

foreach ($rules as $rule)
{
	if (isset(privacy_profile_fields()[$rule->name()]) && !privacy_profile_collects($rule->name())) continue;
	$this->rule($rule);
}

$this->success(function($profile){
			$profile->commit();
			notify($this->lang('Profil modifié'));
			refresh();
		})
		->success(function($profile){
			if ($profile->sex === 'unspecified') $profile->set('sex', NULL);
		});
