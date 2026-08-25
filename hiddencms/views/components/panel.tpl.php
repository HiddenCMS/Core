<div class="card<?php echo $class ? ' '.utf8_htmlentities($class) : '' ?>"<?php echo $attrs ?>>
	<?php if ($title || $actions): ?>
		<div class="card-header">
			<?php echo $title ?>
			<?php if ($actions): ?><span class="float-right"><?php echo $actions ?></span><?php endif ?>
		</div>
	<?php endif ?>
	<?php if ($body !== ''): ?>
		<?php if ($body_wrap): ?>
			<div class="card-body"><?php echo $body ?></div>
		<?php else: ?>
			<?php echo $body ?>
		<?php endif ?>
	<?php endif ?>
	<?php if ($footer): ?>
		<div class="card-footer"><?php echo $footer ?></div>
	<?php endif ?>
</div>
