<div class="ui fluid card<?php echo $class ? ' '.utf8_htmlentities($class) : '' ?>"<?php echo $attrs ?>>
	<?php if ($title || $actions): ?>
		<div class="content">
			<div class="header">
				<?php echo $title ?>
				<?php if ($actions): ?><span class="right floated"><?php echo $actions ?></span><?php endif ?>
			</div>
		</div>
	<?php endif ?>

	<?php if ($body !== ''): ?>
		<?php if ($body_wrap): ?>
			<div class="content"><?php echo $body ?></div>
		<?php else: ?>
			<?php echo $body ?>
		<?php endif ?>
	<?php endif ?>

	<?php if ($footer): ?>
		<div class="extra content"><?php echo $footer ?></div>
	<?php endif ?>
</div>
