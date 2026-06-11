<div class="ui modal">
	<div class="header">
		<?php echo icon($icon).' '.$title ?>
		<i class="close icon" data-dismiss="modal" aria-label="<?php echo $this->lang('Fermer') ?>"></i>
	</div>
	<div class="content">
		<?php echo $users ?>
	</div>
	<div class="actions">
		<button type="button" class="ui secondary button" data-dismiss="modal"><?php echo $this->lang('Fermer') ?></button>
	</div>
</div>
