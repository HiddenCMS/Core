<div id="<?php echo $id ?>" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog<?php echo $size ?>">
		<?php if ($has_form): ?>
			<form action="<?php echo $form_action ?>" method="<?php echo $form_method ?>">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title"><?php echo $header ?></h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo $close_label ?>"><span aria-hidden="true">&times;</span></button>
					</div>
					<?php if ($body !== ''): ?>
						<?php if ($body_wrap): ?>
							<div class="modal-body"><?php echo $body ?></div>
						<?php else: ?>
							<?php echo $body ?>
						<?php endif ?>
					<?php endif ?>
					<?php if ($actions): ?>
						<div class="modal-footer"><?php echo $actions ?></div>
					<?php endif ?>
				</div>
			</form>
		<?php else: ?>
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title"><?php echo $header ?></h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo $close_label ?>"><span aria-hidden="true">&times;</span></button>
				</div>
				<?php if ($body !== ''): ?>
					<?php if ($body_wrap): ?>
						<div class="modal-body"><?php echo $body ?></div>
					<?php else: ?>
						<?php echo $body ?>
					<?php endif ?>
				<?php endif ?>
				<?php if ($actions): ?>
					<div class="modal-footer"><?php echo $actions ?></div>
				<?php endif ?>
			</div>
		<?php endif ?>
	</div>
</div>
