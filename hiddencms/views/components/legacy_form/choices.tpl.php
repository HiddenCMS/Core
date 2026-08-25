<input type="hidden" name="<?php echo utf8_htmlentities($hidden_name); ?>" value="" />
<?php foreach ($items as $item): ?>
	<?php if ($inline): ?><label class="radio-inline"><?php else: ?><div class="checkbox"><label><?php endif ?>
		<input type="<?php echo $item['type']; ?>" name="<?php echo utf8_htmlentities($item['name']); ?>" value="<?php echo utf8_htmlentities($item['value']); ?>"<?php echo $item['checked'] ? ' checked="checked"' : ''; ?> />
		<?php echo $item['label']; ?>
	<?php if ($inline): ?></label><?php else: ?></label></div><?php endif ?>
<?php endforeach; ?>
