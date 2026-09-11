window.HiddenCMS = window.HiddenCMS || {};

window.HiddenCMS.initTinyMce = function(context){
	var $context = $(context);
	var $editors = $context.is('textarea.wysiwyg') ? $context : $context.find('textarea.wysiwyg');

	$editors.each(function(){
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
			plugins: 'autolink lists link image code table',
			toolbar: 'undo redo | blocks | bold italic underline strikethrough | bullist numlist | link image mediatheque | code | removeformat',
			convert_urls: false,
			file_picker_types: 'file image',
			file_picker_callback: function(callback, value, meta){
				window.HiddenCMS.openFilePicker({
					accept: meta.filetype === 'image' ? 'image' : 'file',
					onSelect: function(file){
						callback(file.url, file.is_image ? {alt: file.name, title: file.name} : {text: file.name, title: file.name});
					}
				});
			},
			setup: function(editor){
				editor.ui.registry.addButton('mediatheque', {
					icon: 'browse',
					tooltip: 'Médiathèque',
					onAction: function(){
						window.HiddenCMS.openFilePicker({
							accept: 'file',
							onSelect: function(file){
								if (file.is_image){
									editor.insertContent('<img src="'+editor.dom.encode(file.url)+'" alt="'+editor.dom.encode(file.name)+'">');
								}
								else {
									editor.insertContent('<a href="'+editor.dom.encode(file.url)+'">'+editor.dom.encode(file.name)+'</a>');
								}
							}
						});
					}
				});

				editor.on('init change keyup undo redo', function(){
					editor.save();
				});
			}
		});
	});
};

form.find('textarea.wysiwyg', function(){
	window.HiddenCMS.initTinyMce(this);
});
