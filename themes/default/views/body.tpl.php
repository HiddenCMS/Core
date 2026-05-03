<header>
	<?php if ($zone = $this->output->region('top')): ?>
	<div class="haut">
		<div class="container">
			<?php echo $zone ?>
		</div>
	</div>
	<?php endif ?>
	<?php if ($zone = $this->output->region('header')): ?>
	<div class="entete">
		<div class="container">
			<?php echo $zone ?>
		</div>
	</div>
	<?php endif ?>
</header>
<section id="avant-contenu">
	<?php if ($zone = $this->output->region('before_content')): ?>
	<div class="container">
		<?php echo $zone ?>
	</div>
	<?php endif ?>
</section>
<section id="contenu">
	<?php if (($zone = $this->output->error()) || ($zone = $this->output->region('content'))): ?>
	<div class="container">
		<?php echo $zone ?>
	</div>
	<?php endif ?>
</section>
<?php if ($zone = $this->output->region('after_content')): ?>
<section id="post-contenu">
	<div class="container">
		<?php echo $zone ?>
	</div>
</section>
<?php endif ?>
<?php if ($zone = $this->output->region('footer')): ?>
<footer>
	<div class="container">
		<?php echo $zone ?>
	</div>
</footer>
<?php endif ?>
