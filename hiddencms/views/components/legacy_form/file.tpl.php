<div data-file-field>
<?php if ($deleted): ?><input type="hidden" name="<?php echo utf8_htmlentities($delete_name); ?>" value="delete" /><?php endif ?>
<?php if ($thumbnail): ?>
	<input class="form-file-delete-value" type="hidden" name="<?php echo utf8_htmlentities($delete_name); ?>" value="delete" disabled />
	<div class="row">
		<div class="col-3" data-file-preview>
			<div class="thumbnail">
				<img src="<?php echo utf8_htmlentities($thumbnail); ?>" class="img-fluid mb-1" alt="" />
				<div class="caption text-center">
					<a class="btn btn-outline-danger btn-block btn-sm form-file-delete" href="#" data-confirm="<?php echo utf8_htmlentities($confirm_label); ?>"><?php echo icon('far fa-trash-alt'); ?> <?php echo $delete_label; ?></a>
				</div>
			</div>
		</div>
		<div class="col-9"><p><?php echo icon('fas fa-download'); ?> <?php echo $upload_label.$info; ?></p><?php echo $input; ?></div>
	</div>
<?php else: ?>
	<div class="legacy-file-input"><p><?php echo icon('fas fa-download'); ?> <?php echo $upload_label.$info; ?></p><?php echo $input; ?></div>
<?php endif ?>
</div>
