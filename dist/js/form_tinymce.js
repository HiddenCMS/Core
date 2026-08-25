form.find('textarea.wysiwyg', function(){
	if (typeof tinymce == 'undefined'){
		return;
	}

	if (this.id){
		var existing = tinymce.get(this.id);

		if (existing){
			existing.remove();
		}
	}

	var rows = parseInt($(this).attr('rows'), 10);

	tinymce.init({
		target: this,
		license_key: 'gpl',
		branding: false,
		promotion: false,
		menubar: false,
		statusbar: false,
		skin: 'oxide',
		content_css: 'default',
		height: isNaN(rows) ? 320 : Math.max(260, rows * 26),
		plugins: 'autolink lists link code table',
		toolbar: 'undo redo | blocks | bold italic underline strikethrough | bullist numlist | link | code | removeformat',
		convert_urls: false,
		setup: function(editor){
			editor.on('init change keyup undo redo', function(){
				editor.save();
			});
		}
	});
});
