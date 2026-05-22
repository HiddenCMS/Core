<?php
$panel_class = trim((string)$class);

if (strpos(' '.$panel_class.' ', ' card ') === FALSE)
{
	$panel_class = trim('card '.$panel_class);
}

$panel_class = trim($panel_class.' hb-panel');
$panel_header = str_replace('card-header', 'card-header hb-panel-header', $header);
$panel_footer = str_replace('card-footer', 'card-footer hb-panel-footer', $footer);
?>
<div class="<?php echo $panel_class ?>"<?php echo $attrs ?>>
	<?php echo $panel_header ?>
	<?php if ($body !== ''): ?>
		<?php if ($body_wrap): ?>
			<div class="card-content hb-panel-body"><?php echo $body ?></div>
		<?php else: ?>
			<?php echo $body ?>
		<?php endif ?>
	<?php endif ?>
	<?php echo $panel_footer ?>
</div>
