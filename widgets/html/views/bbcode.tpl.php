<div class="field">
	<label for="widget-html-content"><?php echo $this->lang('Contenu') ?></label>
	<textarea class="wysiwyg" id="widget-html-content" name="settings[content]" rows="12"><?php if (isset($content)) echo $content ?></textarea>
</div>
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
