<?php $closed = (bool)$this->config->maintenance; ?>
<div class="maintenance-status<?php echo $closed ? ' is-closed' : ' is-open' ?>">
	<div class="ui toggle checkbox maintenance-toggle">
		<input type="checkbox"<?php echo $closed ? ' checked="checked"' : '' ?> autocomplete="off">
		<label>
			<span class="maintenance-status-label"><?php echo $this->lang($closed ? 'Maintenance enabled' : 'Site open') ?></span>
		</label>
	</div>
	<p class="maintenance-status-description">
		<?php echo $this->lang($closed ? 'Visitors can see the maintenance page.' : 'The site is accessible to visitors.') ?>
	</p>
</div>
