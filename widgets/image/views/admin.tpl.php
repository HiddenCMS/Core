<?php $settings = array_merge(['image_id' => 0, 'alt' => '', 'caption' => '', 'link' => '', 'ratio' => 'auto'], $settings); ?>
<?php echo $image_field ?>
<div class="two fields">
	<div class="field">
		<label for="widget-image-alt"><?php echo $this->lang('Texte alternatif') ?></label>
		<input type="text" id="widget-image-alt" name="settings[alt]" value="<?php echo $settings['alt'] ?>" />
	</div>
	<div class="field">
		<label for="widget-image-ratio"><?php echo $this->lang('Format') ?></label>
		<select class="ui fluid selection dropdown" id="widget-image-ratio" name="settings[ratio]">
			<?php foreach (['auto' => 'Original', 'landscape' => 'Paysage', 'square' => 'Carré', 'portrait' => 'Portrait'] as $value => $label): ?>
			<option value="<?php echo $value ?>"<?php if ($settings['ratio'] == $value) echo ' selected="selected"' ?>><?php echo $this->lang($label) ?></option>
			<?php endforeach ?>
		</select>
	</div>
</div>
<div class="field">
	<label for="widget-image-caption"><?php echo $this->lang('Légende') ?></label>
	<input type="text" id="widget-image-caption" name="settings[caption]" value="<?php echo $settings['caption'] ?>" />
</div>
<div class="field">
	<label for="widget-image-link"><?php echo $this->lang('Lien au clic') ?></label>
	<input type="text" id="widget-image-link" name="settings[link]" value="<?php echo $settings['link'] ?>" placeholder="https:// ou /page" />
</div>
