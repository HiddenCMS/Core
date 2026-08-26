<form id="live-editor-settings-form" class="ui form">
	<div class="ui mini stackable fluid steps live-editor-settings-steps">
		<div class="active step" data-step="widget">
			<?php echo icon('fas fa-th-large') ?>
			<div class="content"><div class="title"><?php echo $this->lang('Widget') ?></div></div>
		</div>
		<div class="step" data-step="type">
			<?php echo icon('fas fa-list-ul') ?>
			<div class="content"><div class="title"><?php echo $this->lang('Type') ?></div></div>
		</div>
		<div class="step" data-step="title">
			<?php echo icon('fas fa-heading') ?>
			<div class="content"><div class="title"><?php echo $this->lang('Titre') ?></div></div>
		</div>
		<div class="step" data-step="settings">
			<?php echo icon('fas fa-sliders-h') ?>
			<div class="content"><div class="title"><?php echo $this->lang('Configuration') ?></div></div>
		</div>
	</div>

	<div class="live-editor-settings-choice" aria-live="polite">
		<div class="live-editor-settings-choice-icon">
			<?php echo icon(isset($icons[$widget]) ? $icons[$widget] : 'fas fa-puzzle-piece') ?>
		</div>
		<div>
			<div class="live-editor-settings-choice-label"><?php echo $this->lang('Widget sélectionné') ?></div>
			<div class="live-editor-settings-choice-name"><?php echo $widgets[$widget] ?></div>
		</div>
	</div>

	<div class="live-editor-settings-panels">
	<div class="live-editor-settings-panel" data-step="widget">
		<input type="hidden" id="live-editor-settings-widget" name="widget" value="<?php echo $widget ?>" />
		<div class="ui cards live-editor-widget-cards" role="listbox" aria-label="<?php echo $this->lang('Widget') ?>">
		<?php foreach ($widgets as $name => $w): ?>
			<button type="button" class="card live-editor-widget-card<?php if ($name == $widget) echo ' active' ?>" data-widget="<?php echo $name ?>" role="option" aria-selected="<?php echo $name == $widget ? 'true' : 'false' ?>">
				<span class="content">
					<span class="live-editor-widget-card-icon"><?php echo icon(isset($icons[$name]) ? $icons[$name] : 'fas fa-puzzle-piece') ?></span>
					<span class="header"><?php echo $w ?></span>
				</span>
			</button>
		<?php endforeach ?>
		</div>
	</div>

	<div class="live-editor-settings-panel" data-step="type">
		<input type="hidden" id="live-editor-settings-type" name="type" value="<?php echo $type ?>" />
		<div class="ui cards live-editor-type-cards" role="listbox" aria-label="<?php echo $this->lang('Type') ?>">
		<?php foreach ($types as $w => $widget_types): ?>
			<?php foreach ($widget_types as $name => $t): ?>
			<button type="button" class="card live-editor-type-card<?php if ($w == $widget && $name == $type) echo ' active' ?>" data-widget="<?php echo $w ?>" data-type="<?php echo $name ?>" role="option" aria-selected="<?php echo $w == $widget && $name == $type ? 'true' : 'false' ?>"<?php if ($w != $widget) echo ' style="display: none;"' ?>>
				<span class="content">
					<span class="live-editor-type-card-icon"><?php echo icon('fas fa-layer-group') ?></span>
					<span class="header"><?php echo $t ?></span>
				</span>
			</button>
			<?php endforeach ?>
		<?php endforeach ?>
		</div>
	</div>

	<div class="live-editor-settings-panel" data-step="title">
		<div class="field">
			<label for="live-editor-settings-title"><?php echo $this->lang('Titre') ?></label>
			<input type="text" id="live-editor-settings-title" name="title" value="<?php echo $title ?>" placeholder="<?php echo $this->lang('Titre par defaut') ?>" />
		</div>
		<div class="field">
			<div class="ui toggle checkbox live-editor-display-title">
				<input type="checkbox" tabindex="0" class="hidden"<?php if ($display_title) echo ' checked="checked"' ?> />
				<label><?php echo $this->lang('Afficher le titre') ?></label>
			</div>
		</div>
	</div>

	<div class="live-editor-settings-panel" data-step="settings">
		<div id="live-editor-settings" data-widget-id="<?php echo $widget_id ?>" data-original-widget="<?php echo $widget ?>" data-original-type="<?php echo $type ?>"></div>
	</div>
	</div>
</form>
