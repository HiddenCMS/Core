<div class="modal fade" id="modalCharte" tabindex="-1" role="dialog" aria-labelledby="modalCharteLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo $this->lang('Close') ?>"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modalCharteLabel"><?php echo $this->lang('Rules') ?></h4>
			</div>
			<div class="modal-body">
				<?php echo bbcode($this->config->registration_charte) ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo $this->lang('Close') ?></button>
				<button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo icon('fas fa-check').' '.$this->lang('I have read the rules') ?></button>
			</div>
		</div>
	</div>
</div>
