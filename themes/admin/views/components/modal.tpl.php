<?php $tag = $has_form ? 'form' : 'div'; ?>
<<?php echo $tag ?> id="<?php echo $id ?>" class="ui <?php echo $semantic_size ? $semantic_size.' ' : '' ?>modal" tabindex="-1" role="dialog" aria-hidden="true"<?php echo $has_form ? ' action="'.$form_action.'" method="'.$form_method.'"' : '' ?>>
	<div class="header">
		<?php echo $header ?>
		<i class="close icon" data-dismiss="modal" aria-label="<?php echo $this->lang('Fermer') ?>"></i>
	</div>

	<?php if ($body !== ''): ?>
		<div class="content"><?php echo $body ?></div>
	<?php endif ?>

	<?php if ($actions !== ''): ?>
		<div class="actions"><?php echo $actions ?></div>
	<?php endif ?>
</<?php echo $tag ?>>
