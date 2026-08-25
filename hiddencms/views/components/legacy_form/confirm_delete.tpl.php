<div class="modal-header">
	<h5 class="modal-title"><?php echo $title; ?></h5>
	<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo $close_label; ?>"><span aria-hidden="true">&times;</span></button>
</div>
<div class="modal-body">
	<?php echo $message; ?>
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo $cancel_label; ?></button>
	<a class="btn btn-danger delete-confirm" href="<?php echo $request_url; ?>" data-form-id="<?php echo $token; ?>" onclick="return confirm_deletion(this);"><?php echo $delete_label; ?></a>
</div>
