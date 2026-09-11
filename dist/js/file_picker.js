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
					+'<button type="button" class="ui icon button files-picker-mkdir" title="Créer un dossier" aria-label="Créer un dossier"><i class="folder plus icon"></i></button>'
					+'<button type="button" class="ui primary button files-picker-upload"><i class="upload icon"></i> Téléverser</button>'
				+'</div>'
				+'<div class="files-picker-navigation">'
					+'<button type="button" class="ui icon button files-picker-up" title="Dossier parent" aria-label="Dossier parent"><i class="level up alternate icon"></i></button>'
					+'<div class="files-picker-breadcrumb" aria-label="Chemin du dossier"></div>'
				+'</div>'
				+'<form class="files-picker-mkdir-form ui form">'
					+'<div class="ui fluid action input"><input type="text" name="folder_name" placeholder="Nom du nouveau dossier" autocomplete="off"><button type="submit" class="ui primary icon button" title="Créer" aria-label="Créer"><i class="folder plus icon"></i></button><button type="button" class="ui icon button files-picker-mkdir-cancel" title="Annuler" aria-label="Annuler"><i class="times icon"></i></button></div>'
				+'</form>'
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
		var directories = [];
		var currentDir = '';
		var breadcrumbs = [];
		var selected = null;
		var $library = $modal.find('.files-picker-library');
		var $search = $modal.find('.files-picker-search input');
		var $upload = $modal.find('.files-picker-upload');
		var $uploadInput = $modal.find('.files-picker-upload-input');
		var $mkdir = $modal.find('.files-picker-mkdir');
		var $mkdirForm = $modal.find('.files-picker-mkdir-form');
		var $mkdirInput = $mkdirForm.find('input[name="folder_name"]');
		var $up = $modal.find('.files-picker-up');
		var $breadcrumb = $modal.find('.files-picker-breadcrumb');
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
			$library.find('[data-file-id]').removeClass('selected').attr('aria-pressed', 'false');
			$library.find('[data-file-id="'+file.id+'"]').addClass('selected').attr('aria-pressed', 'true');
			$current.html('<span class="files-picker-current-preview">'+iconFor(file)+'</span>'
				+'<span><small>Fichier sélectionné</small><strong>'+escapeHtml(file.name)+'</strong></span>');
			$confirm.removeClass('disabled');
		};

		var clearSelection = function(){
			selected = null;
			$current.empty();
			$confirm.addClass('disabled');
		};

		var renderBreadcrumbs = function(){
			$breadcrumb.empty();

			breadcrumbs.forEach(function(item, index){
				if (index){
					$breadcrumb.append('<i class="angle right icon"></i>');
				}

				$('<button type="button"/>')
					.addClass(index === breadcrumbs.length - 1 ? 'active' : '')
					.text(item.name)
					.on('click', function(){ loadDirectory(item.path); })
					.appendTo($breadcrumb);
			});

			$up.prop('disabled', currentDir === '');
		};

		var render = function(){
			var query = $.trim($search.val()).toLowerCase();
			var visibleDirectories = directories.filter(function(directory){
				return !query || directory.name.toLowerCase().indexOf(query) !== -1;
			});
			var visibleFiles = files.filter(function(file){
				return !query || file.name.toLowerCase().indexOf(query) !== -1 || file.extension.toLowerCase().indexOf(query) !== -1;
			});

			if (!visibleDirectories.length && !visibleFiles.length){
				$library.html('<div class="files-picker-empty"><i class="far fa-folder-open icon"></i><strong>Dossier vide</strong><span>Créez un dossier, téléversez un fichier ou modifiez votre recherche.</span></div>');
				return;
			}

			var $grid = $('<div class="files-picker-grid"/>');

			visibleDirectories.forEach(function(directory){
				$('<button type="button" class="files-picker-card files-picker-folder"/>')
					.attr('data-folder-path', directory.path)
					.append('<span class="files-picker-card-preview"><i class="far fa-folder fa-fw icon"></i></span>')
					.append('<span class="files-picker-card-name">'+escapeHtml(directory.name)+'</span>')
					.append('<span class="files-picker-card-meta">Dossier</span>')
					.on('click', function(){ loadDirectory(directory.path); })
					.appendTo($grid);
			});

			visibleFiles.forEach(function(file){
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

		var loadDirectory = function(dir, initial){
			$library.html('<div class="ui active centered inline loader"></div>');
			setStatus('');
			$search.val('');

			$.ajax({
				url: endpoint(),
				data: {
					accept: options.accept,
					dir: initial ? undefined : (dir || ''),
					selected_id: initial ? options.selectedId : 0
				},
				dataType: 'json',
				cache: false
			}).done(function(response){
				currentDir = response.current_dir || '';
				breadcrumbs = response.breadcrumbs || [{name: 'Racine', path: ''}];
				directories = response.directories || [];
				files = response.files || [];
				$upload.toggle(response.can_upload !== false);
				$mkdir.toggle(response.can_mkdir !== false);
				$mkdirForm.removeClass('visible');
				clearSelection();
				renderBreadcrumbs();
				render();

				if (initial && options.selectedId){
					var current = files.filter(function(file){ return String(file.id) === String(options.selectedId); })[0];
					if (current){ select(current); }
				}
			}).fail(function(xhr){
				var response = xhr.responseJSON || {};
				$library.html('<div class="ui negative message">'+escapeHtml(response.error || 'La médiathèque n’a pas pu être chargée.')+'</div>');
			});
		};

		$search.on('input', render);
		$confirm.on('click', choose);
		$upload.on('click', function(){ $uploadInput.trigger('click'); });
		$up.on('click', function(){
			var parts = currentDir.split('/');
			parts.pop();
			loadDirectory(parts.join('/'));
		});
		$mkdir.on('click', function(){
			$mkdirForm.toggleClass('visible');
			setStatus('');

			if ($mkdirForm.hasClass('visible')){
				$mkdirInput.val('').trigger('focus');
			}
		});
		$mkdirForm.on('click', '.files-picker-mkdir-cancel', function(){
			$mkdirForm.removeClass('visible');
			$mkdirInput.val('');
		});
		$mkdirForm.on('submit', function(event){
			event.preventDefault();

			var name = $.trim($mkdirInput.val());

			if (!name){
				setStatus('Veuillez saisir un nom de dossier.', 'error');
				return;
			}

			$mkdirForm.addClass('loading');
			setStatus('Création du dossier en cours…');

			$.ajax({
				url: endpoint('mkdir'),
				type: 'POST',
				data: {dir: currentDir, name: name},
				dataType: 'json'
			}).done(function(response){
				if (response.error){
					setStatus(response.error, 'error');
					return;
				}

				loadDirectory(response.folder.path);
			}).fail(function(xhr){
				var response = xhr.responseJSON || {};
				setStatus(response.error || 'Le dossier n’a pas pu être créé.', 'error');
			}).always(function(){
				$mkdirForm.removeClass('loading');
			});
		});

		$uploadInput.on('change', function(){
			if (!this.files || !this.files[0]){
				return;
			}

			var data = new FormData();
			data.append('file', this.files[0]);
			data.append('accept', options.accept);
			data.append('dir', currentDir);
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

		loadDirectory('', true);
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
