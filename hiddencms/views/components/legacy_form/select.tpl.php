<select class="form-control" id="<?php echo utf8_htmlentities($id); ?>" name="<?php echo utf8_htmlentities($name); ?>">
	<?php if ($placeholder): ?><option></option><?php endif; ?>
	<?php foreach ($choices as $choice): ?>
	<option value="<?php echo utf8_htmlentities($choice['value']); ?>"<?php echo $choice['selected'] ? ' selected="selected"' : ''; ?>><?php echo $choice['label']; ?></option>
	<?php endforeach; ?>
</select>
