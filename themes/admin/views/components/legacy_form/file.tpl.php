<div data-file-field>
<?php if ($deleted): ?><input type="hidden" name="<?php echo utf8_htmlentities($delete_name); ?>" value="delete" /><?php endif ?>
<?php if ($thumbnail): ?>
	<input class="form-file-delete-value" type="hidden" name="<?php echo utf8_htmlentities($delete_name); ?>" value="delete" disabled />
	<div class="ui stackable grid">
		<div class="four wide column" data-file-preview>
			<img class="ui fluid image" src="<?php echo utf8_htmlentities($thumbnail); ?>" alt="" />
			<a class="ui negative fluid mini button form-file-delete" href="#" data-confirm="<?php echo utf8_htmlentities($confirm_label); ?>"><?php echo icon('far fa-trash-alt'); ?> <?php echo $delete_label; ?></a>
		</div>
		<div class="twelve wide column"><p><?php echo icon('fas fa-download'); ?> <?php echo $upload_label.$info; ?></p><?php echo $input; ?></div>
	</div>
<?php else: ?>
	<div class="ui segment"><p><?php echo icon('fas fa-download'); ?> <?php echo $upload_label.$info; ?></p><?php echo $input; ?></div>
<?php endif ?>
</div>
