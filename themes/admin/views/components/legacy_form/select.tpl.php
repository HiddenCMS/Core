<?php if ($multiple): ?><input type="hidden" name="<?php echo utf8_htmlentities($name); ?>" value="" /><?php endif; ?>
<select class="ui<?php echo $multiple ? ' fluid multiple' : ''; ?> search selection dropdown" id="<?php echo utf8_htmlentities($id); ?>" name="<?php echo utf8_htmlentities($name); ?>"<?php echo $multiple ? ' multiple="multiple"' : ''; ?>>
	<?php if ($placeholder): ?><option></option><?php endif; ?>
	<?php foreach ($choices as $choice): ?>
	<option value="<?php echo utf8_htmlentities($choice['value']); ?>"<?php echo $choice['selected'] ? ' selected="selected"' : ''; ?>><?php echo $choice['label']; ?></option>
	<?php endforeach; ?>
</select>
