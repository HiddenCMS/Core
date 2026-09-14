<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

$rules = [
		$this->form_text('first_name')
					->title('First name')
					->size('col-6'),
		$this->form_text('last_name')
					->title('Name')
					->size('col-6'),
		$this->form_date('date_of_birth')
					->title('Date of birth')
					->check(function($post, $data){
						if (!is_empty($data['date_of_birth']) && $this->date($data['date_of_birth'])->diff() > 0)
						{
							return (string)$this->lang('Invalid date of birth');
						}
					})
					->size('col-6'),
		$this->form_select('sex')
					->title('Gender')
					->data([
						'unspecified' => (string)$this->lang('Prefer not to say'),
						'female' => (string)$this->lang('Female'),
						'male'   => (string)$this->lang('Male')
					])
					->value($this->model()->sex ?: 'unspecified')
					->check(function($post){
						if (!in_array($post['sex'] ?? '', ['unspecified', 'female', 'male'], TRUE))
						{
							return (string)$this->lang('Invalid choice');
						}
					})
					->size('col-6'),
		$this->form_select('country')
					->title('Country')
					->placeholder((string)$this->lang('Not specified'))
					->data(get_countries())
					->size('col-6'),
		$this->form_text('location')
					->title('Location')
					->size('col-6'),
		$this->form_text('quote')
					->title('Quote'),
		$this->form_editor('signature')
					->title('Signature')
					->value(bbcode($this->model()->signature))
					->rows(5)
];

foreach ($rules as $rule)
{
	if (isset(privacy_profile_fields()[$rule->name()]) && !privacy_profile_collects($rule->name())) continue;
	$this->rule($rule);
}

$this->success(function($profile){
			$profile->commit();
			notify($this->lang('Profile updated'));
			refresh();
		})
		->success(function($profile){
			if ($profile->sex === 'unspecified') $profile->set('sex', NULL);
		});
