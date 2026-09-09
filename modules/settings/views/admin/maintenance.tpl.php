<?php $closed = (bool)$this->config->maintenance; ?>
<div class="maintenance-status<?php echo $closed ? ' is-closed' : ' is-open' ?>">
	<div class="ui toggle checkbox maintenance-toggle">
		<input type="checkbox"<?php echo $closed ? ' checked="checked"' : '' ?> autocomplete="off">
		<label>
			<span class="maintenance-status-label"><?php echo $this->lang($closed ? 'Maintenance activée' : 'Site ouvert') ?></span>
		</label>
	</div>
	<p class="maintenance-status-description">
		<?php echo $this->lang($closed ? 'La page de maintenance est visible par les visiteurs.' : 'Le site est accessible aux visiteurs.') ?>
	</p>
</div>
