<header>
	<?php if ($zone = $this->output->region('top', 0)): ?>
	<div class="haut">
		<div class="container">
			<?php echo $zone ?>
		</div>
	</div>
	<?php endif ?>
	<?php if ($zone = $this->output->region('header', 1)): ?>
	<div class="entete">
		<div class="container">
			<?php echo $zone ?>
		</div>
	</div>
	<?php endif ?>
</header>
<section id="avant-contenu">
	<?php if ($zone = $this->output->region('before_content', 2)): ?>
	<div class="container">
		<?php echo $zone ?>
	</div>
	<?php endif ?>
</section>
<section id="contenu">
	<?php if (($zone = $this->output->error()) || ($zone = $this->output->zone(3))): ?>
	<div class="container">
		<?php echo $zone ?>
	</div>
	<?php endif ?>
</section>
<?php if ($zone = $this->output->region('after_content', 4)): ?>
<section id="post-contenu">
	<div class="container">
		<?php echo $zone ?>
	</div>
</section>
<?php endif ?>
<?php if ($zone = $this->output->region('footer', 5)): ?>
<footer>
	<div class="container">
		<?php echo $zone ?>
	</div>
</footer>
<?php endif ?>
