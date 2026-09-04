<div class="user-profile">
	<?php echo $user->avatar() ?>
	<h4 class="mb-3"><?php echo $user->username ?></h4>
	<?php
		if (($profile = $user->profile()) && $profile())
		{
			$sex = privacy_profile_value($profile, 'sex');
			$date_of_birth = privacy_profile_value($profile, 'date_of_birth');
			$country = privacy_profile_value($profile, 'country');
			$location = privacy_profile_value($profile, 'location');
			echo $this	->array
						->append_if($quote = $profile->quote, '<i class="text-muted">'.$quote.'</i>')
						->append(trim(privacy_profile_value($profile, 'first_name').' '.privacy_profile_value($profile, 'last_name')))
						->append_if($sex || $date_of_birth, function() use ($sex, $date_of_birth){
							return $this->label($date_of_birth ? $this->lang('%d an|%d ans', $age = $date_of_birth->interval('today')->y, $age) : ($sex == 'female' ? 'Femme' : 'Homme'), $sex ? ($sex == 'female' ? 'fas fa-venus' : 'fas fa-mars').' '.$sex : 'fas fa-birthday-cake');
						})
						->append_if($location || $country, function() use ($location, $country){
							return $this->label($this->no_translate($location) ?: (get_countries()[$country] ?? ''), $country && ($flag = image('flags/'.$country.'.png', $this->theme('default'))) ? '<img src="'.$flag.'" alt="" />' : 'fas fa-map-marker-alt');
						})
						->filter()
						->each(function($a){
							return '<h6>'.$a.'</h6>';
						});

			$socials = $this	->array([
									['website',   'fas fa-globe',       ''],
									['linkedin',  'fab fa-linkedin-in', 'https://www.linkedin.com/in/'],
									['github',    'fab fa-github',      'https://github.com/'],
									['instagram', 'fab fa-instagram',   'https://www.instagram.com/'],
									['twitch',    'fab fa-twitch',      'https://www.twitch.tv/']
								])
								->filter(function($a) use ($profile){
									return $profile->{$a[0]};
								})
								->each(function($a) use ($profile){
									return '<a href="'.$a[2].$profile->{$a[0]}.'" class="btn '.$a[0].'" target="_blank">'.icon($a[1]).'</a>';
								});
		}
	?>
	<?php if (isset($socials) && !$socials->empty()): ?><div class="socials"><?php echo $socials ?></div><?php endif ?>
	<?php if ($this->user() && $this->user != $user) echo $this->button()->title('Contacter')->icon('far fa-envelope')->color('dark')->style('btn-block')->url('user/messages/compose/'.$user->url()) ?>
</div>
