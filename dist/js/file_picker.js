(function($){
	'use strict';

	window.HiddenCMS = window.HiddenCMS || {};

	if (window.HiddenCMS.filePickerLoaded){
		window.HiddenCMS.initFilePickers(document);
		return;
	}

	window.HiddenCMS.filePickerLoaded = true;

	var endpoint = function(action){
		var match = window.location.pathname.match(/^(.*?)\/admin(?:\/|$)/);
		var base = match ? match[1] : '';

		return base+'/admin/ajax/files/picker'+(action ? '/'+action : '');
	};

	var escapeHtml = function(value){
		return $('<div/>').text(value == null ? '' : value).html();
	};

	var iconFor = function(file){
		if (file.is_image){
			return '<img src="'+escapeHtml(file.url)+'" alt="" loading="lazy">';
		}

		return '<i class="far fa-file fa-fw icon"></i>';
	};

	var createModal = function(options){
		var title = options.accept === 'image' ? 'Choisir une image' : 'Choisir un fichier';
		var $modal = $('<div class="ui large modal files-picker-modal" role="dialog" aria-modal="true">'
			+'<div class="header"><i class="far fa-folder-open icon"></i> '+title+'<i class="close icon" aria-label="Fermer"></i></div>'
			+'<div class="content">'
				+'<div class="files-picker-tools">'
					+'<div class="ui icon fluid input files-picker-search"><input type="search" placeholder="Rechercher un fichier"><i class="search icon"></i></div>'
					+'<input class="files-picker-upload-input" type="file"'+(options.accept === 'image' ? ' accept="image/*"' : '')+'>'
					+'<button type="button" class="ui primary button files-picker-upload"><i class="upload icon"></i> Téléverser</button>'
				+'</div>'
				+'<div class="files-picker-status" role="status" aria-live="polite"></div>'
				+'<div class="files-picker-library"><div class="ui active centered inline loader"></div></div>'
				+'<div class="files-picker-current" aria-live="polite"></div>'
			+'</div>'
			+'<div class="actions">'
				+'<button type="button" class="ui button cancel">Annuler</button>'
				+'<button type="button" class="ui primary disabled button files-picker-confirm"><i class="check icon"></i> Choisir</button>'
			+'</div>'
		+'</div>').appendTo('body');

		var files = [];
		var selected = null;
		var $library = $modal.find('.files-picker-library');
		var $search = $modal.find('.files-picker-search input');
		var $upload = $modal.find('.files-picker-upload');
		var $uploadInput = $modal.find('.files-picker-upload-input');
		var $status = $modal.find('.files-picker-status');
		var $current = $modal.find('.files-picker-current');
		var $confirm = $modal.find('.files-picker-confirm');

		var setStatus = function(message, type){
			$status.removeClass('visible error success').empty();

			if (message){
				$status.addClass('visible '+(type || '')).text(message);
			}
		};

		var select = function(file){
			selected = file;
			$library.find('.files-picker-card').removeClass('selected').attr('aria-pressed', 'false');
			$library.find('[data-file-id="'+file.id+'"]').addClass('selected').attr('aria-pressed', 'true');
			$current.html('<span class="files-picker-current-preview">'+iconFor(file)+'</span>'
				+'<span><small>Fichier sélectionné</small><strong>'+escapeHtml(file.name)+'</strong></span>');
			$confirm.removeClass('disabled');
		};

		var render = function(){
			var query = $.trim($search.val()).toLowerCase();
			var visible = files.filter(function(file){
				return !query || file.name.toLowerCase().indexOf(query) !== -1 || file.extension.toLowerCase().indexOf(query) !== -1;
			});

			if (!visible.length){
				$library.html('<div class="files-picker-empty"><i class="far fa-folder-open icon"></i><strong>Aucun fichier trouvé</strong><span>Téléversez un fichier ou modifiez votre recherche.</span></div>');
				return;
			}

			var $grid = $('<div class="files-picker-grid"/>');

			visible.forEach(function(file){
				$('<button type="button" class="files-picker-card" aria-pressed="false"/>')
					.attr('data-file-id', file.id)
					.append('<span class="files-picker-card-preview">'+iconFor(file)+'</span>')
					.append('<span class="files-picker-card-name">'+escapeHtml(file.name)+'</span>')
					.append('<span class="files-picker-card-meta">'+escapeHtml([file.extension, file.size].filter(Boolean).join(' · '))+'</span>')
					.on('click', function(){ select(file); })
					.on('dblclick', function(){ select(file); choose(); })
					.appendTo($grid);
			});

			$library.empty().append($grid);

			if (selected){
				$library.find('[data-file-id="'+selected.id+'"]').addClass('selected').attr('aria-pressed', 'true');
			}
		};

		var choose = function(){
			if (!selected){
				return;
			}

			options.onSelect(selected);
			$modal.modal('hide');
		};

		$search.on('input', render);
		$confirm.on('click', choose);
		$upload.on('click', function(){ $uploadInput.trigger('click'); });

		$uploadInput.on('change', function(){
			if (!this.files || !this.files[0]){
				return;
			}

			var data = new FormData();
			data.append('file', this.files[0]);
			data.append('accept', options.accept);
			$upload.addClass('loading disabled');
			setStatus('Téléversement en cours…');

			$.ajax({
				url: endpoint('upload'),
				type: 'POST',
				data: data,
				processData: false,
				contentType: false,
				dataType: 'json'
			}).done(function(response){
				if (response.error){
					setStatus(response.error, 'error');
					return;
				}

				files.unshift(response.file);
				$search.val('');
				render();
				select(response.file);
				setStatus('Le fichier a été ajouté à la médiathèque.', 'success');
			}).fail(function(xhr){
				var response = xhr.responseJSON || {};
				setStatus(response.error || 'Le téléversement a échoué.', 'error');
			}).always(function(){
				$upload.removeClass('loading disabled');
				$uploadInput.val('');
			});
		});

		$modal.modal({
			allowMultiple: true,
			autofocus: false,
			observeChanges: true,
			onVisible: function(){ $search.trigger('focus'); },
			onHidden: function(){ $modal.remove(); }
		}).modal('show');

		$.ajax({
			url: endpoint(),
			data: {accept: options.accept},
			dataType: 'json',
			cache: false
		}).done(function(response){
			files = response.files || [];
			$upload.toggle(response.can_upload !== false);
			render();

			if (options.selectedId){
				var current = files.filter(function(file){ return String(file.id) === String(options.selectedId); })[0];
				if (current){ select(current); }
			}
		}).fail(function(){
			$library.html('<div class="ui negative message">La médiathèque n’a pas pu être chargée.</div>');
		});
	};

	window.HiddenCMS.openFilePicker = function(options){
		options = $.extend({accept: 'file', selectedId: 0, onSelect: $.noop}, options || {});
		createModal(options);
	};

	window.HiddenCMS.initFilePickers = function(context){
		var $context = $(context || document);
		var $fields = $context.is('[data-file-picker]') ? $context : $context.find('[data-file-picker]');

		$fields.each(function(){
			var $field = $(this);

			if ($field.data('file-picker-bound')){
				return;
			}

			$field.data('file-picker-bound', true);
			var $input = $field.find('input[type="hidden"]').first();
			var $selection = $field.find('.files-picker-selection');
			var $preview = $field.find('.files-picker-selection-preview');
			var $name = $field.find('[data-file-picker-name]');
			var $clear = $field.find('[data-file-picker-clear]');
			var emptyName = $name.text();

			$field.on('click', '[data-file-picker-open]', function(){
				window.HiddenCMS.openFilePicker({
					accept: $field.data('accept') || 'file',
					selectedId: $input.val(),
					onSelect: function(file){
						$input.val(file.id).trigger('change');
						$name.text(file.name);
						$preview.html(iconFor(file));
						$selection.addClass('has-file');
						$clear.show();
					}
				});
			});

			$clear.on('click', function(){
				$input.val('').trigger('change');
				$name.text(emptyName);
				$preview.html('<i class="far fa-image fa-fw icon"></i>');
				$selection.removeClass('has-file');
				$clear.hide();
			});
		});
	};

	$('body').on('nf.load', function(e){ window.HiddenCMS.initFilePickers(e.target); });
	$(function(){ window.HiddenCMS.initFilePickers(document); });
})(jQuery);
