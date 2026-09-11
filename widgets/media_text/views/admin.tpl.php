<?php $settings = array_merge(['image_id' => 0, 'alt' => '', 'eyebrow' => '', 'heading' => '', 'content' => '', 'button' => '', 'link' => '', 'position' => 'left', 'align' => 'center'], $settings); ?>
<?php echo $image_field ?>
<div class="field">
	<label for="widget-media-text-alt"><?php echo $this->lang('Texte alternatif') ?></label>
	<input type="text" id="widget-media-text-alt" name="settings[alt]" value="<?php echo $settings['alt'] ?>" />
</div>
<div class="two fields">
	<div class="field">
		<label for="widget-media-text-eyebrow"><?php echo $this->lang('Sur-titre') ?></label>
		<input type="text" id="widget-media-text-eyebrow" name="settings[eyebrow]" value="<?php echo $settings['eyebrow'] ?>" />
	</div>
	<div class="field">
		<label for="widget-media-text-heading"><?php echo $this->lang('Titre') ?></label>
		<input type="text" id="widget-media-text-heading" name="settings[heading]" value="<?php echo $settings['heading'] ?>" />
	</div>
</div>
<div class="field">
	<label for="widget-media-text-content"><?php echo $this->lang('Contenu') ?></label>
	<textarea class="wysiwyg" id="widget-media-text-content" name="settings[content]" rows="10"><?php echo $settings['content'] ?></textarea>
</div>
<div class="two fields">
	<div class="field">
		<label for="widget-media-text-button"><?php echo $this->lang('Libellé du bouton') ?></label>
		<input type="text" id="widget-media-text-button" name="settings[button]" value="<?php echo $settings['button'] ?>" />
	</div>
	<div class="field">
		<label for="widget-media-text-link"><?php echo $this->lang('Lien du bouton') ?></label>
		<input type="text" id="widget-media-text-link" name="settings[link]" value="<?php echo $settings['link'] ?>" placeholder="https:// ou /page" />
	</div>
</div>
<div class="two fields">
	<div class="field">
		<label><?php echo $this->lang('Position de l’image') ?></label>
		<select class="ui fluid selection dropdown" name="settings[position]">
			<option value="left"<?php if ($settings['position'] == 'left') echo ' selected="selected"' ?>><?php echo $this->lang('Gauche') ?></option>
			<option value="right"<?php if ($settings['position'] == 'right') echo ' selected="selected"' ?>><?php echo $this->lang('Droite') ?></option>
		</select>
	</div>
	<div class="field">
		<label><?php echo $this->lang('Alignement vertical') ?></label>
		<select class="ui fluid selection dropdown" name="settings[align]">
			<?php foreach (['start' => 'Haut', 'center' => 'Centre', 'end' => 'Bas'] as $value => $label): ?>
			<option value="<?php echo $value ?>"<?php if ($settings['align'] == $value) echo ' selected="selected"' ?>><?php echo $this->lang($label) ?></option>
			<?php endforeach ?>
		</select>
	</div>
</div>
<script type="text/javascript" src="<?php echo js('file_picker.js') ?>"></script>
<script type="text/javascript" src="<?php echo js('form_tinymce.js') ?>"></script>
<script type="text/javascript">
	(function(){
		var initialize = function(){
			window.HiddenCMS.initTinyMce($('#live-editor-settings-form'));
		};

		if (window.tinymce){
			initialize();
		}
		else {
			$.getScript('https://cdn.jsdelivr.net/npm/tinymce@6.8.5/tinymce.min.js', initialize);
		}

		$('#live-editor-settings-form').on('nf.live-editor-settings.submit', function(){
			if (window.tinymce){
				tinymce.triggerSave();
			}
		});
	})();
</script>
