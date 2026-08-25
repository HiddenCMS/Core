<div class="<?php echo $fast_mode ? 'text-center' : 'form-group row' ?>">
	<?php if (!$fast_mode): ?><div class="offset-3 col-9"><?php endif ?>
		<?php echo implode(' ', $buttons); ?>
	<?php if (!$fast_mode): ?></div><?php endif ?>
</div>
