<?php
$label_tag = !empty($label_tag) ? $label_tag : 'label';
$popover = array_filter([$description, $error]);
$content_size = $size && preg_match('/^col-([1-9])$/', $size) ? $size : 'col-sm-9';
?>
<div class="form-group row<?php echo $has_error ? ' has-error' : ''; ?>">
	<?php if ($fast_mode): ?>
		<div class="col"><?php echo $content; ?></div>
	<?php else: ?>
		<<?php echo $label_tag ?> class="col-sm-3 col-form-label col-form-label-sm"<?php if ($label_for): ?> for="<?php echo utf8_htmlentities($label_for) ?>"<?php endif ?><?php if ($popover): ?> data-toggle="popover" data-trigger="hover" data-placement="right" data-html="true" data-content="<?php echo utf8_htmlentities(implode('<br /><br />', $popover)); ?>"<?php endif ?>>
			<?php if ($description): ?><span class="text-info"><?php echo icon('fas fa-info-circle'); ?></span><?php endif ?>
			<?php if ($error): ?><span class="text-danger"><?php echo icon('fas fa-exclamation-triangle'); ?></span><?php endif ?>
			<?php echo $label; ?><?php if ($display_required && $is_required): ?><em>*</em><?php endif ?>
		</<?php echo $label_tag ?>>
		<div class="<?php echo $content_size; ?>"><?php echo $content; ?></div>
	<?php endif ?>
</div>
