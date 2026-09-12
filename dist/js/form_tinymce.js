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
			menubar: 'file edit view insert format tools table help',
			menu: {insert: {title: 'Insert', items: 'image link media pdfreader | charmap emoticons hr | pagebreak nonbreaking anchor insertdatetime'}},
			statusbar: true,
			skin: 'oxide',
			content_css: 'default',
			height: isNaN(rows) ? 320 : Math.max(260, rows * 26),
			plugins: 'accordion advlist anchor autolink autosave charmap code codesample directionality emoticons fullscreen help image insertdatetime link lists media nonbreaking pagebreak preview quickbars searchreplace table visualblocks visualchars wordcount',
			toolbar: [
				'undo redo restoredraft | blocks fontfamily fontsize | bold italic underline strikethrough subscript superscript | forecolor backcolor | removeformat',
				'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | ltr rtl | link unlink anchor | image media mediatheque pdfreader | table',
				'hr charmap emoticons nonbreaking insertdatetime pagebreak accordion codesample | searchreplace visualblocks visualchars | code preview fullscreen help'
			],
			toolbar_mode: 'wrap',
			image_caption: true,
			image_advtab: true,
			image_uploadtab: false,
			browser_spellcheck: true,
			autosave_ask_before_unload: true,
			extended_valid_elements: 'iframe[src|title|width|height|style|loading|data-hb-pdf]',
			convert_urls: false,
			setup: function(editor){
				var insertPdf = function(file){
					editor.insertContent('<iframe data-hb-pdf="'+Number(file.id)+'" src="'+editor.dom.encode(file.url)+'" title="Lecteur PDF" width="100%" height="640" style="border:0" loading="lazy"></iframe><p><a href="'+editor.dom.encode(file.url)+'" target="_blank" rel="noopener noreferrer">Ouvrir le PDF</a></p>');
				};
				var choosePdf = function(){
					window.HiddenCMS.openFilePicker({accept: 'pdf', onSelect: insertPdf});
				};
				editor.ui.registry.addButton('pdfreader', {icon: 'document-properties', tooltip: 'Insérer un lecteur PDF', onAction: choosePdf});
				editor.ui.registry.addMenuItem('pdfreader', {icon: 'document-properties', text: 'Insérer un lecteur PDF', onAction: choosePdf});
				editor.ui.registry.addContextToolbar('pdfreader', {predicate: function(node){ return node.nodeName === 'IFRAME' && node.hasAttribute('data-hb-pdf'); }, items: 'pdfreader', position: 'node', scope: 'node'});
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
								else if (String(file.extension).toLowerCase() === 'pdf'){
									insertPdf(file);
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
