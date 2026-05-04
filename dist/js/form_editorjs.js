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

	var saveTimer = null;
	var isSaving = false;
	var savePending = false;

	var sync = function(editor){
		if (isSaving){
			savePending = true;
			return;
		}

		isSaving = true;
		var finalize = function(){
			isSaving = false;

			if (savePending){
				savePending = false;
				sync(editor);
			}
		};

		editor.save().then(function(data){
			$textarea.val(JSON.stringify(data)).trigger('change');
			finalize();
		}).catch(function(){
			$textarea.trigger('change');
			finalize();
		});
	};

	var scheduleSync = function(editor){
		if (saveTimer){
			clearTimeout(saveTimer);
		}

		saveTimer = setTimeout(function(){
			sync(editor);
		}, 150);
	};

	var pickClass = function(candidates){
		for (var i = 0; i < candidates.length; i++){
			if (typeof window[candidates[i]] !== 'undefined'){
				return window[candidates[i]];
			}
		}

		return null;
	};

	var HeaderTool = pickClass(['Header']);
	var ListTool = pickClass(['EditorjsList', 'List']);
	var QuoteTool = pickClass(['Quote']);
	var CodeTool = pickClass(['CodeTool', 'Code']);
	var DelimiterTool = pickClass(['Delimiter']);
	var tools = {};

	if (HeaderTool){
		tools.header = {
			class: HeaderTool,
			inlineToolbar: ['link']
		};
	}

	if (ListTool){
		tools.list = {
			class: ListTool,
			inlineToolbar: ['link']
		};
	}

	if (QuoteTool){
		tools.quote = {
			class: QuoteTool,
			inlineToolbar: ['link']
		};
	}

	if (CodeTool){
		tools.code = {
			class: CodeTool
		};
	}

	if (DelimiterTool){
		tools.delimiter = {
			class: DelimiterTool
		};
	}

	var editor = new EditorJS({
		holder: holderId,
		autofocus: false,
		minHeight: 140,
		placeholder: 'Ecris ton contenu...',
		inlineToolbar: ['bold', 'italic', 'link'],
		tools: tools,
		data: parseData(decode($textarea.val())),
		onChange: function(){
			scheduleSync(editor);
		}
	});

	editor.isReady.then(function(){
		sync(editor);
	});

	$form.on('submit', function(){
		sync(editor);
	});
});
