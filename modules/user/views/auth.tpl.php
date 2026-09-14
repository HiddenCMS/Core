<section class="user-auth-page">
	<div class="user-auth-box">
		<header class="user-auth-header">
			<span class="user-auth-icon"><?php echo icon($icon) ?></span>
			<div>
				<h1><?php echo $this->lang($title) ?></h1>
				<p><?php echo $this->lang($description) ?></p>
			</div>
		</header>

		<div class="user-auth-form">
			<?php echo $form ?>
		</div>

		<?php if (!empty($links)): ?>
		<nav class="user-auth-links" aria-label="<?php echo $this->lang('Account navigation') ?>">
			<?php foreach ($links as $link): ?>
			<a href="<?php echo $link['url'] ?>"><?php echo $this->lang($link['title']) ?></a>
			<?php endforeach ?>
		</nav>
		<?php endif ?>
	</div>
</section>
