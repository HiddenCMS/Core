<?php
$panel_class = trim(str_replace('card', 'hb-panel', $class));
$panel_header = str_replace('card-header', 'hb-panel-header', $header);
$panel_footer = str_replace('card-footer', 'hb-panel-footer', $footer);
?>
<div class="<?php echo $panel_class ?>"<?php echo $attrs ?>>
	<?php echo $panel_header ?>
	<?php if ($body !== ''): ?>
		<?php if ($body_wrap): ?>
			<div class="hb-panel-body"><?php echo $body ?></div>
		<?php else: ?>
			<?php echo $body ?>
		<?php endif ?>
	<?php endif ?>
	<?php echo $panel_footer ?>
</div>
