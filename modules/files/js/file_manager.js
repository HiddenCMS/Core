$(function(){
	var formatSize = function(size){
		if (!size)
		{
			return '0 o';
		}

		var units = ['o', 'Ko', 'Mo', 'Go'];
		var value = size;
		var unit = 0;

		while (value >= 1024 && unit < units.length - 1)
		{
			value = value / 1024;
			unit++;
		}

		return (unit ? value.toFixed(1) : Math.round(value))+' '+units[unit];
	};

	var init = function(){
		$('.files-path-dropdown').each(function(){
			var $dropdown = $(this);

			if ($dropdown.data('files-dropdown-bound'))
			{
				return;
			}

			$dropdown.data('files-dropdown-bound', true);
			$dropdown.dropdown({
				fullTextSearch: 'exact',
				forceSelection: true
			});
		});

		$('.files-manager').each(function(){
			var $manager = $(this);

			if ($manager.data('files-selection-bound'))
			{
				return;
			}

			$manager.data('files-selection-bound', true);

			var $bar = $('[data-files-selection]');
			var $count = $bar.find('.files-selection-count');
			var $buttons = $bar.find('.files-selection-action');
			var $singleButtons = $bar.find('[data-files-selection-single]');
			var $selectAll = $manager.find('.files-select-all');
			var $items = $manager.find('.files-select-item');

			var selected = function(){
				return $items.filter(':checked').map(function(){
					var $item = $(this);

					return {
						path: $item.val(),
						name: $item.data('name') || $item.val()
					};
				}).get();
			};

			var update = function(){
				var paths = selected();
				var total = $items.length;
				var checked = paths.length;

				$buttons.prop('disabled', !checked);
				$singleButtons.prop('disabled', checked !== 1);
				$manager.find('[data-file-row]').removeClass('is-selected');

				$items.filter(':checked').closest('[data-file-row]').addClass('is-selected');

				if (!checked)
				{
					$count.text('Aucun element selectionne');
				}
				else
				{
					$count.text(checked+' element'+(checked > 1 ? 's' : '')+' selectionne'+(checked > 1 ? 's' : ''));
				}

				$selectAll.prop('checked', total > 0 && checked === total);
				$selectAll.prop('indeterminate', checked > 0 && checked < total);
			};

			var fillSelectionForm = function($modal){
				var paths = selected();
				var $inputs = $modal.find('.files-selected-inputs');
				var $summary = $modal.find('.files-selected-summary');
				var $name = $modal.find('input[name="name"]');

				$inputs.empty();

				paths.forEach(function(item){
					$('<input/>', {
						type: 'hidden',
						name: 'paths[]',
						value: item.path
					}).appendTo($inputs);
				});

				$summary.text(paths.length+' element'+(paths.length > 1 ? 's' : '')+' selectionne'+(paths.length > 1 ? 's' : ''));
				$name.val(paths.length === 1 ? paths[0].name : '');
			};

			$selectAll.on('change', function(){
				$items.prop('checked', this.checked);
				update();
			});

			$items.on('change', update);

			$manager.find('[data-file-row]').on('click', function(e){
				if ($(e.target).is('a, input, button, label') || $(e.target).closest('a, input, button, label').length)
				{
					return;
				}

				var $checkbox = $(this).find('.files-select-item');
				$checkbox.prop('checked', !$checkbox.prop('checked')).trigger('change');
			});

			$('.files-move-modal, .files-rename-modal, .files-delete-modal').on('show.bs.modal', function(e){
				var paths = selected();

				if (!paths.length || ($(this).hasClass('files-rename-modal') && paths.length !== 1))
				{
					e.preventDefault();
					return;
				}

				fillSelectionForm($(this));
			});

			update();
		});

		$('.files-mkdir-modal').each(function(){
			var $modal = $(this);

			if ($modal.data('files-mkdir-bound'))
			{
				return;
			}

			$modal.data('files-mkdir-bound', true);

			$modal.on('shown.bs.modal', function(){
				$modal.find('input[name="name"]').trigger('focus');
			});
		});

		$('.files-upload-modal').each(function(){
			var $modal = $(this);

			if ($modal.data('files-upload-bound'))
			{
				return;
			}

			$modal.data('files-upload-bound', true);

			var $input = $modal.find('.files-upload-input');
			var $dropzone = $modal.find('.files-upload-dropzone');
			var $list = $modal.find('.files-upload-list');
			var files = [];

			var syncInput = function(){
				if (typeof DataTransfer === 'undefined')
				{
					return;
				}

				var transfer = new DataTransfer();

				files.forEach(function(file){
					transfer.items.add(file);
				});

				$input[0].files = transfer.files;
			};

			var render = function(){
				$list.empty();

				files.forEach(function(file, index){
					$('<li/>')
						.append($('<span/>', {
							'class': 'files-upload-list-name',
							text: file.name
						}))
						.append($('<span/>', {
							'class': 'files-upload-list-meta',
							text: formatSize(file.size)
						}))
						.append($('<button/>', {
							type: 'button',
							'class': 'ui negative mini icon button',
							title: 'Retirer',
							'aria-label': 'Retirer',
							html: '<i class="fas fa-times"></i>'
						}).on('click', function(){
							files.splice(index, 1);
							syncInput();
							render();
						}))
						.appendTo($list);
				});
			};

			var setFiles = function(fileList){
				files = Array.prototype.slice.call(fileList || []);
				syncInput();
				render();
			};

			$dropzone.on('click', function(){
				$input.trigger('click');
			});

			$input.on('change', function(){
				setFiles(this.files);
			});

			$dropzone.on('dragenter dragover', function(e){
				e.preventDefault();
				e.stopPropagation();
				$dropzone.addClass('is-dragover');
			});

			$dropzone.on('dragleave dragend drop', function(e){
				e.preventDefault();
				e.stopPropagation();
				$dropzone.removeClass('is-dragover');
			});

			$dropzone.on('drop', function(e){
				var event = e.originalEvent;

				if (event && event.dataTransfer && event.dataTransfer.files.length)
				{
					setFiles(event.dataTransfer.files);
				}
			});

			$modal.on('hidden.bs.modal', function(){
				files = [];
				$input.val('');
				render();
				$dropzone.removeClass('is-dragover');
			});

			$modal.find('.files-upload-form').on('submit', function(e){
				if (!$input[0].files.length)
				{
					e.preventDefault();
					$dropzone.addClass('is-dragover');
				}
			});
		});
	};

	$('body').on('nf.load', init);

	init();
});
