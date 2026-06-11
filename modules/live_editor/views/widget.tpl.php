<form id="live-editor-settings-form" class="ui form">
	<div class="border-light">
		<div class="form-group fields">
			<label for="live-editor-settings-widget" class="four wide field"><?php echo $this->lang('Widget') ?></label>
			<div class="eight wide field">
				<select id="live-editor-settings-widget" class="ui fluid dropdown" name="widget">
				<?php foreach ($widgets as $name => $w): ?>
					<option value="<?php echo $name ?>"<?php if ($name == $widget) echo ' selected="selected"' ?>><?php echo $w ?></option>
				<?php endforeach ?>
				</select>
			</div>
		</div>
		<div class="form-group fields"<?php if (empty($types[$widget])) echo ' style="display: none;"' ?>>
			<label for="live-editor-settings-type" class="four wide field"><?php echo $this->lang('Type') ?></label>
			<div class="eight wide field">
				<select id="live-editor-settings-type" class="ui fluid dropdown" name="type">
				<?php foreach ($types as $w => $types): ?>
					<?php foreach ($types as $name => $t): ?>
						<option value="<?php echo $name ?>" data-widget="<?php echo $w ?>"<?php if ($w != $widget) echo ' style="display: none;"'; else if ($name == $type) echo ' selected="selected"' ?>><?php echo $t ?></option>
					<?php endforeach ?>
				<?php endforeach ?>
				</select>
			</div>
		</div>
		<div class="form-group fields"<?php if ($widget == 'module') echo ' style="display: none;"' ?>>
			<label for="live-editor-settings-title" class="four wide field"><?php echo $this->lang('Titre') ?></label>
			<div class="twelve wide field">
				<input type="text" class="ui input" id="live-editor-settings-title" name="title" value="<?php echo $title ?>" placeholder="<?php echo $this->lang('Titre par defaut') ?>" />
			</div>
		</div>
	</div>
	<div id="live-editor-settings" data-widget-id="<?php echo $widget_id ?>" data-original-widget="<?php echo $widget ?>" data-original-type="<?php echo $type ?>"></div>
</form>
