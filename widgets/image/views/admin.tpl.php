<?php $settings = array_merge(['image_id' => 0, 'alt' => '', 'caption' => '', 'link' => '', 'ratio' => 'auto'], $settings); ?>
<?php echo $image_field ?>
<div class="field">
	<div class="ui toggle checkbox">
		<input type="hidden" name="settings[content_only]" value="0" />
		<input type="checkbox" id="widget-image-content-only" name="settings[content_only]" value="1"<?php if (!empty($settings['content_only'])) echo ' checked="checked"' ?> />
		<label for="widget-image-content-only"><?php echo $this->lang('Content only (no card or header)') ?></label>
	</div>
</div>
<div class="two fields">
	<div class="field">
		<label for="widget-image-alt"><?php echo $this->lang('Alternative text') ?></label>
		<input type="text" id="widget-image-alt" name="settings[alt]" value="<?php echo $settings['alt'] ?>" />
	</div>
	<div class="field">
		<label for="widget-image-ratio"><?php echo $this->lang('Format') ?></label>
		<select class="ui fluid selection dropdown" id="widget-image-ratio" name="settings[ratio]">
			<?php foreach (['auto' => 'Original', 'landscape' => (string)$this->lang('Landscape'), 'square' => (string)$this->lang('Square'), 'portrait' => 'Portrait'] as $value => $label): ?>
			<option value="<?php echo $value ?>"<?php if ($settings['ratio'] == $value) echo ' selected="selected"' ?>><?php echo $this->lang($label) ?></option>
			<?php endforeach ?>
		</select>
	</div>
</div>
<div class="field">
	<label for="widget-image-caption"><?php echo $this->lang('Caption') ?></label>
	<input type="text" id="widget-image-caption" name="settings[caption]" value="<?php echo $settings['caption'] ?>" />
</div>
<div class="field">
	<label for="widget-image-link"><?php echo $this->lang('Click-through link') ?></label>
	<input type="text" id="widget-image-link" name="settings[link]" value="<?php echo $settings['link'] ?>" placeholder="<?php echo utf8_htmlentities((string)$this->lang('https:// or /page')) ?>" />
</div>
