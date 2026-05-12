<div id="<?php echo $id ?>" class="modal fade hb-modal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog<?php echo $size ?>" role="document">
		<div class="modal-content">
			<?php if ($has_form): ?>
				<form action="<?php echo $form_action ?>" method="<?php echo $form_method ?>" class="hb-modal-form">
					<?php echo $content ?>
				</form>
			<?php else: ?>
				<?php echo $content ?>
			<?php endif ?>
		</div>
	</div>
</div>
