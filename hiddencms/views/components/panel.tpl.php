<div class="<?php echo $class ?>"<?php echo $attrs ?>>
	<?php echo $header ?>
	<?php if ($body !== ''): ?>
		<?php if ($body_wrap): ?>
			<div class="content"><?php echo $body ?></div>
		<?php else: ?>
			<?php echo $body ?>
		<?php endif ?>
	<?php endif ?>
	<?php echo $footer ?>
</div>

