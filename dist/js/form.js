var form = new function(){
	var _forms = [];
	var _load = {};

	this.load = function($form, force){
		if (typeof force != 'undefined' && force){
			load = true;
		} else if ($.inArray($form, _forms) == -1){
			_forms.push($form);
			load = true;
		}

		if (load){
			$.each(_load, function(find, callback){
				$form.find(find).each(function(){
					callback.apply(this, [$form]);
				});
			});
		}
	};

	this.submit = function($form){
		var d = $.Deferred();

		if (window.tinymce && typeof tinymce.triggerSave == 'function'){
			tinymce.triggerSave();
		}

		$.ajax({
			url: $form[0].action,
			type: $form[0].method,
			data: new FormData($form[0]),
			processData: false,
			contentType: false,
			success: modal.exec(function(data){
				if (typeof data.form != 'undefined'){
					if (window.tinymce){
						$form.find('textarea.wysiwyg').each(function(){
							var editor = tinymce.get(this.id);

							if (editor){
								editor.remove();
							}
						});
					}

					var $formBody = $form.children('.content:first');

					if (!$formBody.length){
						$formBody = $form.find('.modal-body:first');
					}

					if (!$formBody.length){
						$formBody = $form;
					}

					$formBody.html(data.form);
					form.load($form, true);
				}

				d.resolve(data);
			}),
			error: function(xhr){
				d.reject(xhr);
			}
		});


		return d.promise();
	};

	this.find = function(find, callback){
		_load[find] = callback;
	};

	return this;
};

$(function(){
	$('form').each(function(){
		form.load($(this));
	});
});

form.find('.ui.dropdown', function(){
	if (typeof $.fn.dropdown == 'function'){
		$(this).dropdown({fullTextSearch: true});
	}
});

form.find('.ui.checkbox', function(){
	if (typeof $.fn.checkbox == 'function'){
		$(this).checkbox();
	}
});
