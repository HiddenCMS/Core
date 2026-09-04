<div class="user-editor">
	<div class="ui secondary pointing menu user-editor-tabs" role="tablist" aria-label="<?php echo $this->lang('Utilisateur'); ?>">
		<?php foreach ($tabs as $name => $tab): ?>
		<button type="button" class="item<?php if ($name === 'account') echo ' active'; ?>" role="tab" id="user-tab-<?php echo $name; ?>" data-tab="<?php echo $name; ?>" aria-controls="user-panel-<?php echo $name; ?>" aria-selected="<?php echo $name === 'account' ? 'true' : 'false'; ?>" tabindex="<?php echo $name === 'account' ? '0' : '-1'; ?>">
			<?php echo icon($tab['icon']); ?><?php echo $this->lang($tab['title']); ?>
		</button>
		<?php endforeach; ?>
	</div>
	<?php foreach ($tabs as $name => $tab): ?>
	<section class="ui tab user-editor-pane<?php if ($name === 'account') echo ' active'; ?>" role="tabpanel" id="user-panel-<?php echo $name; ?>" data-tab="<?php echo $name; ?>" aria-labelledby="user-tab-<?php echo $name; ?>" tabindex="0">
		<?php echo $tab['content']; ?>
	</section>
	<?php endforeach; ?>
</div>
