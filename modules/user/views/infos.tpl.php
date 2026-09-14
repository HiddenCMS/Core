<div class="row">
	<div class="col-4">
		<b><?php echo $this->lang('Registered since') ?></b><br />
		<?php echo $user->registration_date ?>
	</div>
	<div class="col-4">
		<b><?php echo $this->lang('Last activity') ?></b><br />
		<?php echo $user->last_activity_date ?>
	</div>
	<div class="col-4">
		<b><?php echo $this->lang('Groups') ?></b><br />
		<?php echo $user->groups() ?>
	</div>
</div>
