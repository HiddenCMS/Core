<link rel="stylesheet" href="<?php echo css('file_picker.css') ?>" />
<script type="text/javascript" src="<?php echo js('file_picker.js') ?>"></script>
<div class="field files-picker-field" data-file-picker data-accept="<?php echo utf8_htmlentities($accept) ?>">
	<label><?php echo $this->lang($label) ?></label>
	<input type="hidden" name="<?php echo utf8_htmlentities($name) ?>" value="<?php echo $file ? (int)$file['id'] : '' ?>" />
	<div class="files-picker-selection<?php echo $file ? ' has-file' : '' ?>">
		<div class="files-picker-selection-preview">
			<?php if ($file && $is_image): ?>
			<img src="<?php echo utf8_htmlentities($url) ?>" alt="" />
			<?php else: ?>
			<?php echo icon($file ? 'far fa-file' : 'far fa-image') ?>
			<?php endif ?>
		</div>
		<div class="files-picker-selection-details">
			<strong data-file-picker-name><?php echo $file ? utf8_htmlentities($file['name']) : $this->lang($empty_label) ?></strong>
			<small><?php echo $this->lang('Choose an existing file or upload a new one.') ?></small>
		</div>
		<div class="files-picker-selection-actions">
			<button type="button" class="ui primary button" data-file-picker-open><?php echo icon('far fa-folder-open').' '.$this->lang('Browse') ?></button>
			<button type="button" class="ui icon button" data-file-picker-clear title="<?php echo $this->lang('Remove') ?>" aria-label="<?php echo $this->lang('Remove') ?>"<?php echo !$file ? ' style="display:none"' : '' ?>><?php echo icon('fas fa-times') ?></button>
		</div>
	</div>
</div>
