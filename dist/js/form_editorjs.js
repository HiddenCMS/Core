form.find('textarea.editor', function($form){
	var $textarea = $(this);

	if ($textarea.data('editorjs-ready') || typeof EditorJS === 'undefined'){
		return;
	}

	$textarea.data('editorjs-ready', true).hide();

	var holderId = $textarea.attr('id') + '_editorjs';
	var $holder = $('<div class="form-editorjs-holder"></div>').attr('id', holderId);

	$textarea.after($holder);

	var parseData = function(value){
		try {
			var parsed = JSON.parse(value);

			if (parsed && $.isArray(parsed.blocks)){
				return parsed;
			}
		}
		catch (e){}

		return {
			blocks: [
				{
					type: 'paragraph',
					data: {
						text: value || ''
					}
				}
			]
		};
	};

	var decode = function(value){
		return $('<textarea />').html(value).text();
	};

	var sync = function(editor){
		return editor.save().then(function(data){
			$textarea.val(JSON.stringify(data)).trigger('change');
		});
	};

	var editor = new EditorJS({
		holder: holderId,
		autofocus: false,
		minHeight: 140,
		data: parseData(decode($textarea.val())),
		onChange: function(){
			sync(editor);
		}
	});

	$form.on('submit', function(){
		sync(editor);
	});
});
