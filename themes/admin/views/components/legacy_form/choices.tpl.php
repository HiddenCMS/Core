<input type="hidden" name="<?php echo utf8_htmlentities($hidden_name); ?>" value="" />
<?php foreach ($items as $item): ?>
	<div class="field<?php if ($inline): ?> inline<?php endif ?>">
		<div class="ui <?php echo $item['type'] === 'radio' ? 'radio ' : ''; ?>checkbox">
			<input type="<?php echo $item['type']; ?>" name="<?php echo utf8_htmlentities($item['name']); ?>" value="<?php echo utf8_htmlentities($item['value']); ?>"<?php echo $item['checked'] ? ' checked="checked"' : ''; ?> />
			<label><?php echo $item['label']; ?></label>
		</div>
	</div>
<?php endforeach; ?>
