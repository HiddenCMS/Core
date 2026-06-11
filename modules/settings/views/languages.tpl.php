<div class="ui centered stackable grid languages">
	<?php foreach ($this->config->langs as $language): ?>
	<div class="sixteen wide mobile eight wide tablet four wide computer column">
		<img class="img-fluid" src="<?php echo image('flags/'.$language->info()->name.'.png') ?>" alt="">
		<a class="btn btn-primary" href="#" data-language="<?php echo $language->info()->name ?>"><?php echo $language->info()->title ?></a>
	</div>
	<?php endforeach ?>
</div>
