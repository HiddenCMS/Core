<?php if ($placeholder !== NULL && $placeholder !== ''): ?>
	<option value=""><?php echo utf8_htmlentities($placeholder); ?></option>
<?php endif; ?>
<?php foreach ($options as $option): ?>
	<option value="<?php echo utf8_htmlentities($option['value']); ?>"<?php echo $option['selected'] ? ' selected' : ''; ?>><?php echo utf8_htmlentities($option['label']); ?></option>
<?php endforeach; ?>
