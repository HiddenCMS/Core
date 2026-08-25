<?php if (isset($element_attrs)): ?>
	<<?php echo $element_tag; ?><?php echo $element_attrs ? ' '.implode(' ', $element_attrs) : ''; ?>><?php echo $element_content; ?></<?php echo $element_tag; ?>>
<?php else: ?>
	<select<?php echo $attrs_output ?>></select>
<?php endif; ?>
