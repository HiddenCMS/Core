<div class="ui small modal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="header">
		<?php echo $title; ?>
		<i class="close icon" data-dismiss="modal" aria-label="<?php echo $close_label; ?>"></i>
	</div>
	<div class="content">
		<?php echo $message; ?>
	</div>
	<div class="actions">
		<button type="button" class="ui button" data-dismiss="modal"><?php echo $cancel_label; ?></button>
		<a class="ui negative button delete-confirm" href="<?php echo $request_url; ?>" data-form-id="<?php echo $token; ?>" onclick="return confirm_deletion(this);"><?php echo $delete_label; ?></a>
	</div>
</div>
