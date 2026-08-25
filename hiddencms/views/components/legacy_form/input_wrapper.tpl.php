<div class="input-group<?php echo $classes ? ' '.utf8_htmlentities($classes) : ''; ?>">
	<div class="input-group-prepend"><span class="input-group-text"><?php echo $icon ?: '<i></i>'; ?></span></div>
	<?php echo $input; ?>
	<?php if ($color): ?><div class="input-group-append"><span class="input-group-text"><?php echo icon('fas fa-eye-dropper'); ?></span></div><?php endif ?>
</div>
