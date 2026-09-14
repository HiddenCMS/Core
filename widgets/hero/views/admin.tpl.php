<?php $settings = array_merge(['image_id' => 0, 'eyebrow' => '', 'heading' => '', 'content' => '', 'button' => '', 'link' => '', 'align' => 'left', 'height' => 'medium', 'overlay' => 'medium'], $settings); ?>
<?php echo $image_field ?>
<div class="field">
	<label for="widget-hero-eyebrow"><?php echo $this->lang('Eyebrow') ?></label>
	<input type="text" id="widget-hero-eyebrow" name="settings[eyebrow]" value="<?php echo $settings['eyebrow'] ?>" />
</div>
<div class="field">
	<label for="widget-hero-heading"><?php echo $this->lang('Main title') ?></label>
	<input type="text" id="widget-hero-heading" name="settings[heading]" value="<?php echo $settings['heading'] ?>" />
</div>
<div class="field">
	<label for="widget-hero-content"><?php echo $this->lang('Text') ?></label>
	<textarea id="widget-hero-content" name="settings[content]" rows="3"><?php echo $settings['content'] ?></textarea>
</div>
<div class="two fields">
	<div class="field">
		<label for="widget-hero-button"><?php echo $this->lang('Button label') ?></label>
		<input type="text" id="widget-hero-button" name="settings[button]" value="<?php echo $settings['button'] ?>" />
	</div>
	<div class="field">
		<label for="widget-hero-link"><?php echo $this->lang('Button link') ?></label>
		<input type="text" id="widget-hero-link" name="settings[link]" value="<?php echo $settings['link'] ?>" placeholder="<?php echo utf8_htmlentities((string)$this->lang('https:// or /page')) ?>" />
	</div>
</div>
<div class="three fields">
	<div class="field">
		<label><?php echo $this->lang('Alignment') ?></label>
		<select class="ui fluid selection dropdown" name="settings[align]">
			<?php foreach (['left' => 'Gauche', 'center' => 'Centre', 'right' => 'Droite'] as $value => $label): ?>
			<option value="<?php echo $value ?>"<?php if ($settings['align'] == $value) echo ' selected="selected"' ?>><?php echo $this->lang($label) ?></option>
			<?php endforeach ?>
		</select>
	</div>
	<div class="field">
		<label><?php echo $this->lang('Height') ?></label>
		<select class="ui fluid selection dropdown" name="settings[height]">
			<?php foreach (['compact' => (string)$this->lang('Compact'), 'medium' => 'Moyenne', 'large' => (string)$this->lang('Large')] as $value => $label): ?>
			<option value="<?php echo $value ?>"<?php if ($settings['height'] == $value) echo ' selected="selected"' ?>><?php echo $this->lang($label) ?></option>
			<?php endforeach ?>
		</select>
	</div>
	<div class="field">
		<label><?php echo $this->lang('Contrast') ?></label>
		<select class="ui fluid selection dropdown" name="settings[overlay]">
			<?php foreach (['light' => (string)$this->lang('Light'), 'medium' => (string)$this->lang('Medium'), 'dark' => (string)$this->lang('Strong')] as $value => $label): ?>
			<option value="<?php echo $value ?>"<?php if ($settings['overlay'] == $value) echo ' selected="selected"' ?>><?php echo $this->lang($label) ?></option>
			<?php endforeach ?>
		</select>
	</div>
</div>
