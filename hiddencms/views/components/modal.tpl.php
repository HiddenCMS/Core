<div id="<?php echo $id ?>" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog<?php echo $size ?>">
		<?php if ($has_form): ?>
			<form action="<?php echo $form_action ?>" method="<?php echo $form_method ?>">
				<div class="modal-content"><?php echo $content ?></div>
			</form>
		<?php else: ?>
			<div class="modal-content"><?php echo $content ?></div>
		<?php endif ?>
	</div>
</div>

