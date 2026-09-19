(function($){
	'use strict';
	// The complete outline stays client-side until the explicit save action.

	var data = JSON.parse(document.getElementById('layout-builder-data').textContent);
	var state = clone(data.layout || []);
	var savedState = serialize(state);
	var undoStack = [];
	var redoStack = [];
	var nextKey = 1;
	var $canvas = $('#layout-builder-canvas');
	var $status = $('#layout-builder-status');

	function clone(value){
		return JSON.parse(JSON.stringify(value));
	}

	function serialize(value){
		return JSON.stringify(value, function(key, item){
			return key === '_key' ? undefined : item;
		});
	}

	function escapeHtml(value){
		return $('<div>').text(value == null ? '' : String(value)).html();
	}

	function ensureKeys(){
		$.each(state, function(z, zone){
			$.each(zone.rows || [], function(r, row){
				row._key = row._key || 'row-'+(nextKey++);
				$.each(row.cols || [], function(c, col){
					col._key = col._key || 'col-'+(nextKey++);
					$.each(col.widgets || [], function(w, widget){
						widget._key = widget._key || 'widget-'+(nextKey++);
					});
				});
			});
		});
	}

	function colWidth(size){
		var match = String(size || '').match(/(?:^|\s)col-(?:lg-)?(\d+)(?:\s|$)/);
		return match ? Math.max(1, Math.min(12, parseInt(match[1], 10))) : 12;
	}

	function renderWidget(widget){
		var title = widget.title || data.widgets[widget.widget] || widget.widget || '<?php echo $this->lang('Widget') ?>';
		var type = widget.type && widget.type !== 'index' ? widget.type : '';
		var iconClass = data.icons[widget.widget] || 'fas fa-puzzle-piece';
		return '<article class="lb-widget" data-key="'+widget._key+'">'+
			'<button type="button" class="lb-drag" title="<?php echo $this->lang('Move') ?>"><?php echo icon('fas fa-grip-vertical') ?></button>'+
			'<span class="lb-widget-icon"><i class="'+escapeHtml(iconClass)+'"></i></span>'+
			'<span class="lb-widget-copy"><strong>'+escapeHtml(title)+'</strong><small>'+escapeHtml(type || widget.widget)+'</small></span>'+
			'<button type="button" class="lb-icon-action lb-edit-widget-style" title="<?php echo $this->lang('Widget appearance') ?>"><?php echo icon('fas fa-paint-brush') ?></button>'+
			'<button type="button" class="lb-icon-action lb-edit-widget" title="<?php echo $this->lang('Settings') ?>"><?php echo icon('fas fa-cog') ?></button>'+
			'<button type="button" class="lb-icon-action lb-remove-widget" title="<?php echo $this->lang('Delete') ?>"><?php echo icon('far fa-trash-alt') ?></button>'+
		'</article>';
	}

	function renderCol(col){
		var width = colWidth(col.size);
		var widgets = $.map(col.widgets || [], renderWidget).join('');
		return '<div class="lb-col" data-key="'+col._key+'" style="flex-basis:'+((width / 12) * 100)+'%;max-width:'+((width / 12) * 100)+'%">'+
			'<div class="lb-col-header">'+
				'<button type="button" class="lb-drag lb-col-drag" title="<?php echo $this->lang('Move') ?>"><?php echo icon('fas fa-grip-vertical') ?></button>'+
				'<span><?php echo $this->lang('Column') ?></span>'+
				'<button type="button" class="lb-icon-action lb-add-widget" title="<?php echo $this->lang('Add a widget') ?>"><?php echo icon('fas fa-plus') ?></button>'+
				'<span class="lb-col-size-label" title="<?php echo $this->lang('Column width') ?>">'+width+'/12</span>'+
				'<button type="button" class="lb-icon-action lb-remove-col" title="<?php echo $this->lang('Delete') ?>"><?php echo icon('far fa-trash-alt') ?></button>'+
			'</div>'+
			'<div class="lb-widgets">'+widgets+'<div class="lb-empty"><?php echo $this->lang('Empty column') ?></div></div>'+
			'<span class="ui-resizable-handle ui-resizable-e lb-col-resize" title="<?php echo $this->lang('Resize the column') ?>" aria-hidden="true"></span>'+
		'</div>';
	}

	function renderRow(row){
		return '<section class="lb-row" data-key="'+row._key+'">'+
			'<header class="lb-row-header">'+
				'<button type="button" class="lb-drag lb-row-drag" title="<?php echo $this->lang('Move') ?>"><?php echo icon('fas fa-grip-vertical') ?></button>'+
				'<strong><?php echo $this->lang('Row') ?></strong>'+
				'<span class="lb-style">'+escapeHtml(row.style || '<?php echo $this->lang('Default style') ?>')+'</span>'+
				'<button type="button" class="lb-icon-action lb-edit-row-style" title="<?php echo $this->lang('Row appearance') ?>"><?php echo icon('fas fa-paint-brush') ?></button>'+
				'<button type="button" class="ui mini button lb-add-col"><?php echo icon('fas fa-plus').' '.$this->lang('Column') ?></button>'+
				'<button type="button" class="lb-icon-action lb-remove-row" title="<?php echo $this->lang('Delete') ?>"><?php echo icon('far fa-trash-alt') ?></button>'+
			'</header>'+
			'<div class="lb-cols">'+$.map(row.cols || [], renderCol).join('')+'</div>'+
		'</section>';
	}

	function renderZone(zone, index){
		return '<section class="lb-zone" data-zone-index="'+index+'">'+
			'<header class="lb-zone-header"><div><span><?php echo $this->lang('Section') ?></span><h2>'+escapeHtml(zone.title)+'</h2></div>'+
			'<button type="button" class="ui small primary button lb-add-row"><?php echo icon('fas fa-plus').' '.$this->lang('Row') ?></button></header>'+
			'<div class="lb-rows">'+$.map(zone.rows || [], renderRow).join('')+'<div class="lb-zone-empty"><?php echo $this->lang('Drop a row here or add one') ?></div></div>'+
		'</section>';
	}

	function render(){
		ensureKeys();
		$canvas.html($.map(state, renderZone).join('') || '<div class="layout-builder-empty"><?php echo $this->lang('No layout is available for this outline.') ?></div>');
		initSortables();
		updateUi();
	}

	function maps(){
		var result = {rows: {}, cols: {}, widgets: {}};
		$.each(state, function(z, zone){
			$.each(zone.rows || [], function(r, row){
				result.rows[row._key] = row;
				$.each(row.cols || [], function(c, col){
					result.cols[col._key] = col;
					$.each(col.widgets || [], function(w, widget){ result.widgets[widget._key] = widget; });
				});
			});
		});
		return result;
	}

	function syncOrder(){
		var lookup = maps();
		$canvas.find('.lb-zone').each(function(){
			var zone = state[parseInt($(this).data('zone-index'), 10)];
			zone.rows = $(this).find('> .lb-rows > .lb-row').map(function(){
				var row = lookup.rows[$(this).data('key')];
				row.cols = $(this).find('> .lb-cols > .lb-col').map(function(){
					var col = lookup.cols[$(this).data('key')];
					col.widgets = $(this).find('> .lb-widgets > .lb-widget').map(function(){ return lookup.widgets[$(this).data('key')]; }).get();
					return col;
				}).get();
				return row;
			}).get();
		});
	}

	function initSortables(){
		$canvas.find('.lb-rows').sortable({connectWith: '.lb-rows', items: '> .lb-row', handle: '.lb-row-drag', placeholder: 'lb-placeholder lb-row-placeholder', stop: sorted});
		$canvas.find('.lb-cols').sortable({connectWith: '.lb-cols', items: '> .lb-col', handle: '.lb-col-drag', placeholder: 'lb-placeholder lb-col-placeholder', stop: sorted});
		$canvas.find('.lb-widgets').sortable({connectWith: '.lb-widgets', items: '> .lb-widget', handle: '.lb-widget .lb-drag', placeholder: 'lb-placeholder lb-widget-placeholder', stop: sorted});
		$canvas.find('.lb-col').each(function(){
			var $col = $(this);
			var $next = $col.next('.lb-col');
			if (!$next.length) return;
			$col.find('.lb-col-resize').on('pointerdown', function(event){
				var lookup = maps();
				var before = clone(state);
				var currentUnits = colWidth(lookup.cols[$col.data('key')].size);
				var nextUnits = colWidth(lookup.cols[$next.data('key')].size);
				var pairUnits = currentUnits + nextUnits;
				var rowWidth = $col.parent().innerWidth();
				var startX = event.clientX;
				var units = currentUnits;
				if (this.setPointerCapture) this.setPointerCapture(event.pointerId);
				$('body').addClass('lb-is-resizing');
				event.preventDefault();
				event.stopPropagation();

				$(document).on('pointermove.lbResize', function(moveEvent){
					var delta = Math.round((moveEvent.clientX - startX) / rowWidth * 12);
					units = Math.max(1, Math.min(pairUnits - 1, currentUnits + delta));
					nextUnits = pairUnits - units;
					var percent = units / 12 * 100;
					var nextPercent = nextUnits / 12 * 100;
					$col.css({flexBasis: percent+'%', maxWidth: percent+'%'});
					$col.find('.lb-col-size-label').text(units+'/12');
					$next.css({flexBasis: nextPercent+'%', maxWidth: nextPercent+'%'}).find('.lb-col-size-label').text(nextUnits+'/12');
				}).one('pointerup.lbResize pointercancel.lbResize', function(){
					$(document).off('.lbResize');
					$('body').removeClass('lb-is-resizing');
					lookup = maps();
					lookup.cols[$col.data('key')].size = 'col-'+units;
					lookup.cols[$next.data('key')].size = 'col-'+(pairUnits - units);
					if (units !== currentUnits) commit(before);
					else render();
				});
			});
		});
	}

	function openWidgetModal(widgetName, colKey, widgetKey){
		if ($('.layout-builder-widget-modal').length) return;
		var lookup = maps(), existing = widgetKey ? lookup.widgets[widgetKey] : null;
		widgetName = existing ? existing.widget : widgetName;
		if (!widgetName) widgetName = Object.keys(data.widgets)[0];
		var selectedType = existing && existing.type ? existing.type : Object.keys(data.types[widgetName] || {})[0] || 'index';
		var $modal = $([
			'<div class="ui large modal live-editor-modal layout-builder-widget-modal" role="dialog">',
				'<div class="header"><?php echo icon('fas fa-cogs').' '.$this->lang('Widget settings') ?><i class="close icon"></i></div>',
				'<div class="content">'+$('#layout-builder-widget-form').html()+'</div>',
				'<div class="actions">',
					'<button type="button" class="ui button cancel"><?php echo $this->lang('Cancel') ?></button>',
					'<button type="button" class="ui button live-editor-previous"><?php echo icon('fas fa-chevron-left').' '.$this->lang('Previous') ?></button>',
					'<button type="button" class="ui primary button live-editor-next"><?php echo $this->lang('Next').' '.icon('fas fa-chevron-right') ?></button>',
					'<button type="button" class="ui primary approve button live-editor-confirm"><?php echo icon('fas fa-check').' '.$this->lang('Apply') ?></button>',
				'</div>',
			'</div>'
		].join('')).appendTo('body');
		var steps = ['widget', 'type', 'title', 'settings'], currentStep = 'widget', settingsRequest = 0;

		$modal.find('#live-editor-settings-widget').val(widgetName);
		$modal.find('#live-editor-settings-type').val(selectedType);
		$modal.find('#live-editor-settings-title').val(existing ? existing.title : '');

		function availableSteps(){
			return $.grep(steps, function(step){ return $modal.find('.live-editor-settings-panel[data-step="'+step+'"]').data('available') !== false; });
		}

		function setStepAvailable(step, available){
			$modal.find('.live-editor-settings-panel[data-step="'+step+'"]').data('available', available);
			$modal.find('.live-editor-settings-steps .step[data-step="'+step+'"]').toggleClass('disabled', !available);
		}

		function showStep(step){
			var available = availableSteps(), index = $.inArray(step, available);
			if (index < 0){ step = available[0]; index = 0; }
			currentStep = step;
			$modal.find('.live-editor-settings-panel').hide().filter('[data-step="'+step+'"]').show();
			$modal.find('.live-editor-settings-steps .step').removeClass('active completed').each(function(){
				var position = $.inArray($(this).data('step'), available);
				if (position >= 0 && position < index) $(this).addClass('completed');
				if (position === index) $(this).addClass('active');
			});
			$modal.find('.live-editor-previous').toggle(index > 0);
			$modal.find('.live-editor-next').toggle(index < available.length - 1);
			$modal.find('.live-editor-confirm').toggle(index === available.length - 1);
		}

		function loadSettings(useExisting){
			var request = ++settingsRequest;
			var settings = useExisting && existing ? existing.settings || {} : {};
			var $settings = $modal.find('#live-editor-settings').html('<div class="ui active centered inline loader"></div>');
			setStepAvailable('settings', false);
			$.post(data.urls.widgetAdmin, {
				widget: $modal.find('#live-editor-settings-widget').val(),
				type: $modal.find('#live-editor-settings-type').val() || 'index',
				settings: JSON.stringify(settings)
			}).done(function(html){
				if (request !== settingsRequest) return;
				$settings.html(html || '');
				setStepAvailable('settings', $.trim(html || '') !== '');
				showStep(currentStep);
			}).fail(function(){
				if (request !== settingsRequest) return;
				$settings.html('<div class="ui negative message"><?php echo $this->lang('Unable to load widget settings.') ?></div>');
				setStepAvailable('settings', true);
				showStep(currentStep);
			});
		}

		function selectWidget(name, keepTitle, useExisting){
			var $widgetCard = $modal.find('.live-editor-widget-card[data-widget="'+name+'"]');
			var $typeCards = $modal.find('.live-editor-type-card').hide().removeClass('active').attr('aria-selected', 'false');
			var $availableTypes = $typeCards.filter('[data-widget="'+name+'"]').show();
			var type = $modal.find('#live-editor-settings-type').val();
			var $selectedType = $availableTypes.filter('[data-type="'+type+'"]');
			if (!$selectedType.length) $selectedType = $availableTypes.first();
			$modal.find('#live-editor-settings-widget').val(name);
			$modal.find('.live-editor-widget-card').removeClass('active').attr('aria-selected', 'false');
			$widgetCard.addClass('active').attr('aria-selected', 'true');
			$modal.find('.live-editor-settings-choice-icon').html($widgetCard.find('.live-editor-widget-card-icon').html());
			$modal.find('.live-editor-settings-choice-name').text($widgetCard.find('.header').text());
			if ($selectedType.length){
				$modal.find('#live-editor-settings-type').val($selectedType.data('type'));
				$selectedType.addClass('active').attr('aria-selected', 'true');
			}
			else $modal.find('#live-editor-settings-type').val('index');
			setStepAvailable('type', $availableTypes.length > 0);
			setStepAvailable('title', name !== 'module');
			if (!keepTitle) $modal.find('#live-editor-settings-title').val('');
			loadSettings(useExisting);
		}

		setStepAvailable('widget', true);
		setStepAvailable('title', true);
		setStepAvailable('type', false);
		setStepAvailable('settings', false);
		$modal.on('click', '.live-editor-widget-card', function(){ selectWidget($(this).data('widget'), false, false); });
		$modal.on('click', '.live-editor-type-card', function(){
			$modal.find('.live-editor-type-card').removeClass('active').attr('aria-selected', 'false');
			$(this).addClass('active').attr('aria-selected', 'true');
			$modal.find('#live-editor-settings-type').val($(this).data('type'));
			loadSettings(false);
		});
		$modal.on('click', '.live-editor-previous, .live-editor-next', function(){
			var available = availableSteps(), index = $.inArray(currentStep, available);
			showStep(available[index + ($(this).hasClass('live-editor-next') ? 1 : -1)]);
		});
		$modal.on('click', '.live-editor-settings-steps .step:not(.disabled)', function(){ showStep($(this).data('step')); });
		var applyWidget = function(){
			var before = clone(state), model = existing || {id: 0, style: null, size: null, settings: {}};
			model.widget = $modal.find('#live-editor-settings-widget').val();
			model.type = $modal.find('#live-editor-settings-type').val() || 'index';
			model.title = $modal.find('#live-editor-settings-title').val();
			model.settings_form = $modal.find('#live-editor-settings :input').serialize();
			model.dirty = true;
			if (!existing) lookup.cols[colKey].widgets.push(model);
			commit(before);
			$modal.modal('hide');
		};
		$modal.find('#live-editor-settings-form').on('submit', function(event){ event.preventDefault(); });
		$modal.modal({
			autofocus: false,
			observeChanges: true,
			onApprove: function(){ applyWidget(); return false; },
			onHidden: function(){ $modal.remove(); }
		}).modal('show');
		selectWidget(widgetName, true, !!existing);
		showStep('widget');
	}

	function openStyleModal(model, templateSelector, title){
		if ($('.layout-builder-style-modal').length) return;
		var before = clone(state), classes = $.grep(String(model.style || '').split(/\s+/), Boolean);
		var modifiers = {}, $modal = $([ 
			'<div class="ui large modal layout-builder-style-modal">',
				'<div class="header"><?php echo icon('fas fa-paint-brush') ?> '+escapeHtml(title)+'<i class="close icon"></i></div>',
				'<div class="content">'+$(templateSelector).html()+'</div>',
				'<div class="actions"><button type="button" class="ui button cancel"><?php echo $this->lang('Cancel') ?></button><button type="button" class="ui primary approve button lb-style-save"><?php echo icon('fas fa-check').' '.$this->lang('Apply') ?></button></div>',
			'</div>'
		].join('')).appendTo('body');

		$modal.find('[data-style-modifier]').each(function(){
			var name = String($(this).data('style-modifier')), enabled = $.inArray(name, classes) !== -1;
			modifiers[name] = enabled;
			classes = $.grep(classes, function(value){ return value !== name; });
			this.checked = $(this).attr('data-style-modifier-inverted') === 'true' ? !enabled : enabled;
		});
		var selected = classes.join(' ');
		$modal.find('[data-style]').each(function(){
			if (String($(this).data('style') || '') === selected) $(this).addClass('active');
		});
		if (!$modal.find('[data-style].active').length) $modal.find('[data-style]').first().addClass('active');

		$modal.on('click', '[data-style]', function(event){
			event.preventDefault();
			selected = String($(this).data('style') || '');
			$modal.find('[data-style]').removeClass('active');
			$(this).addClass('active');
		});
		$modal.on('change', '[data-style-modifier]', function(){
			var inverted = $(this).attr('data-style-modifier-inverted') === 'true';
			modifiers[String($(this).data('style-modifier'))] = inverted ? !this.checked : this.checked;
		});
		var applyStyle = function(){
			var values = $.grep(selected.split(/\s+/), Boolean);
			$.each(modifiers, function(name, enabled){ if (enabled) values.push(name); });
			model.style = values.join(' ');
			commit(before);
			$modal.modal('hide');
		};
		$modal.modal({
			autofocus: false,
			onApprove: function(){ applyStyle(); return false; },
			onHidden: function(){ $modal.remove(); }
		}).modal('show');
	}

	function sorted(){
		var before = clone(state);
		syncOrder();
		commit(before);
	}

	function commit(previous){
		undoStack.push(previous);
		if (undoStack.length > 50) undoStack.shift();
		redoStack = [];
		render();
	}

	function updateUi(){
		var changed = serialize(state) !== savedState || undoStack.length > 0;
		$status.text(changed ? '<?php echo $this->lang('Unsaved changes') ?>' : '<?php echo $this->lang('No unsaved changes') ?>').toggleClass('dirty', changed);
		$('#layout-builder-save').prop('disabled', !changed);
		$('#layout-builder-undo').prop('disabled', !undoStack.length);
		$('#layout-builder-redo').prop('disabled', !redoStack.length);
	}

	function rebalance(cols){
		var count = cols.length;
		if (!count) return;
		var base = Math.floor(12 / count), remainder = 12 % count;
		$.each(cols, function(index, col){ col.size = 'col-'+(base + (index < remainder ? 1 : 0)); });
	}

	$canvas.on('click', '.lb-add-row', function(){
		var before = clone(state);
		state[$(this).closest('.lb-zone').data('zone-index')].rows.push({style: 'row-default', cols: [{size: 'col-12', widgets: []}]});
		commit(before);
	});

	$canvas.on('click', '.lb-add-col', function(){
		var before = clone(state), key = $(this).closest('.lb-row').data('key'), row = maps().rows[key];
		row.cols.push({size: 'col-12', widgets: []});
		rebalance(row.cols);
		commit(before);
	});

	$canvas.on('click', '.lb-add-widget', function(){
		openWidgetModal(null, $(this).closest('.lb-col').data('key'));
	});

	$canvas.on('click', '.lb-edit-widget', function(){
		var $widget = $(this).closest('.lb-widget');
		openWidgetModal(null, $widget.closest('.lb-col').data('key'), $widget.data('key'));
	});

	$canvas.on('click', '.lb-edit-row-style', function(){
		openStyleModal(maps().rows[$(this).closest('.lb-row').data('key')], '#layout-builder-row-styles', '<?php echo $this->lang('Row appearance') ?>');
	});

	$canvas.on('click', '.lb-edit-widget-style', function(){
		openStyleModal(maps().widgets[$(this).closest('.lb-widget').data('key')], '#layout-builder-widget-styles', '<?php echo $this->lang('Widget appearance') ?>');
	});

	$canvas.on('click', '.lb-remove-row, .lb-remove-col, .lb-remove-widget', function(){
		if (!window.confirm('<?php echo $this->lang('Delete this item?') ?>')) return;
		var before = clone(state), lookup = maps(), $button = $(this);
		if ($button.hasClass('lb-remove-row')){
			var rowKey = $button.closest('.lb-row').data('key');
			$.each(state, function(z, zone){ zone.rows = $.grep(zone.rows, function(row){ return row._key !== rowKey; }); });
		}
		else if ($button.hasClass('lb-remove-col')){
			var colKey = $button.closest('.lb-col').data('key');
			$.each(lookup.rows, function(key, row){ row.cols = $.grep(row.cols, function(col){ return col._key !== colKey; }); });
		}
		else {
			var widgetKey = $button.closest('.lb-widget').data('key');
			$.each(lookup.cols, function(key, col){ col.widgets = $.grep(col.widgets, function(widget){ return widget._key !== widgetKey; }); });
		}
		commit(before);
	});

	$('#layout-builder-undo').on('click', function(){
		if (!undoStack.length) return;
		redoStack.push(clone(state)); state = undoStack.pop(); render();
	});
	$('#layout-builder-redo').on('click', function(){
		if (!redoStack.length) return;
		undoStack.push(clone(state)); state = redoStack.pop(); render();
	});

	$('#layout-builder-save').on('click', function(){
		var $button = $(this).addClass('loading').prop('disabled', true), result = null;
		$.post(data.urls.save, {outline_id: data.outlineId, layout: serialize(state)}).done(function(response){
			try {
				if (typeof response === 'string') response = JSON.parse(response);
			}
			catch (error){
				response = null;
			}
			if (!response || !response.ok){
				result = {type: 'error', message: '<?php echo $this->lang('Unable to save the layout') ?>'};
				return;
			}
			state = clone(response.layout || state); ensureKeys();
			savedState = serialize(state); undoStack = []; redoStack = [];
			render();
			result = {type: 'saved', message: response.message || '<?php echo $this->lang('Layout saved') ?>'};
		}).fail(function(){
			result = {type: 'error', message: '<?php echo $this->lang('Unable to save the layout') ?>'};
		}).always(function(){
			$button.removeClass('loading');
			updateUi();
			$status.removeClass('saved error');
			if (result){
				$status.text(result.message).addClass(result.type);
				if (result.type === 'saved') window.setTimeout(function(){ $status.removeClass('saved'); updateUi(); }, 1800);
			}
		});
	});

	$('#layout-builder-outline').on('change', function(){
		if (serialize(state) !== savedState && !window.confirm('<?php echo $this->lang('Discard unsaved changes?') ?>')){
			return;
		}
		window.location.href = this.value;
	});

	window.addEventListener('beforeunload', function(event){
		if (serialize(state) !== savedState){ event.preventDefault(); event.returnValue = ''; }
	});

	render();
})(jQuery);
