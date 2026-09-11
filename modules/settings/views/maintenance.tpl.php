<div class="maintenance-page">
	<header class="maintenance-header">
		<h2 class="maintenance-brand">
			<?php if ($this->config->maintenance_logo): ?>
				<img src="<?php echo HB()->model2('file', $this->config->maintenance_logo)->path() ?>" class="maintenance-logo" alt="<?php echo utf8_htmlentities($this->config->name) ?>" />
			<?php else: ?>
				<?php echo utf8_htmlentities($this->config->name) ?>
			<?php endif ?>
		</h2>
		<nav class="maintenance-nav" aria-label="<?php echo $this->lang('Navigation secondaire') ?>">
			<?php
				foreach ([
					'behance'    => 'Behance',
					'deviantart' => 'DeviantArt',
					'dribble'    => 'Dribble',
					'facebook'   => 'Facebook',
					'flickr'     => 'Flickr',
					'github'     => 'GitHub',
					'google'     => 'Google+',
					'instagram'  => 'Instagram',
					'steam'      => 'Steam',
					'twitch'     => 'Twitch',
					'twitter'    => 'Twitter',
					'youtube'    => 'YouTube'
				] as $name => $title)
				{
					if ($url = $this->config->{'nf_social_'.$name})
					{
						echo '<a class="maintenance-link" href="'.utf8_htmlentities($url).'" data-toggle="tooltip" title="'.$title.'">'.icon('fab fa-'.$name).'</a>';
					}
				}
			?>
			<?php if ($this->user()): ?>
				<span class="maintenance-user"><?php echo utf8_htmlentities($this->user->username) ?></span>
			<?php endif ?>
			<?php echo $this->user()
				? '<a href="'.url('user/logout').'" class="maintenance-link">'.icon('fas fa-times').' '.$this->lang('Déconnexion').'</a>'
				: '<a href="'.url('user/login').'" class="maintenance-link">'.icon('fas fa-sign-in-alt').' '.$this->lang('Se connecter').'</a>' ?>
		</nav>
	</header>

	<main class="maintenance-content" role="main">
		<h1><?php echo utf8_htmlentities($this->config->maintenance_title ?: $this->lang('Site en maintenance')) ?></h1>
		<?php if ($content = $this->config->maintenance_content): ?>
			<div class="maintenance-message"><?php echo bbcode($content) ?></div>
		<?php else: ?>
			<p class="maintenance-message"><?php echo $this->lang('Nous effectuons actuellement une opération de maintenance. Le site sera de nouveau disponible prochainement.') ?></p>
		<?php endif ?>
		<?php if ($this->config->maintenance_opening): ?>
			<div id="countdown" class="countdownHolder" data-timestamp="<?php echo $this->date($this->config->maintenance_opening)->timestamp() ?>"></div>
		<?php endif ?>
	</main>

	<footer class="maintenance-footer">
		<?php echo $this->widget('copyright')->output()->style('card-transparent') ?>
	</footer>
</div>
