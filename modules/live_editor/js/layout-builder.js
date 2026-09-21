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
	var loadedWidgetScripts = {};

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

	function injectWidgetSettings($container, html){
		var holder = document.createElement('div');
		holder.innerHTML = html || '';
		var scripts = $(holder).find('script').remove().toArray();
		$container.empty().append($(holder).contents());
		var sequence = $.Deferred().resolve().promise();

		$.each(scripts, function(index, script){
			sequence = sequence.then(function(){
				var src = script.getAttribute('src');
				if (src){
					var absoluteSrc = $('<a>').attr('href', src)[0].href;
					var alreadyLoaded = loadedWidgetScripts[absoluteSrc] || $('script[src]').filter(function(){ return this.src === absoluteSrc; }).length > 0;
					if (alreadyLoaded) return;
					return $.ajax({url: absoluteSrc, dataType: 'script', cache: true}).done(function(){ loadedWidgetScripts[absoluteSrc] = true; });
				}
				$.globalEval(script.text || script.textContent || script.innerHTML || '');
			});
		});

		return sequence;
	}

	function loadWidgetScript(src){
		var absoluteSrc = $('<a>').attr('href', src)[0].href;
		if (loadedWidgetScripts[absoluteSrc]) return $.Deferred().resolve().promise();
		loadedWidgetScripts[absoluteSrc] = true;
		return $.ajax({url: absoluteSrc, dataType: 'script', cache: true}).fail(function(){ delete loadedWidgetScripts[absoluteSrc]; });
	}

	function initializeWidgetEditors($container){
		if (window.HiddenCMS && typeof window.HiddenCMS.initFilePickers === 'function'){
			window.HiddenCMS.initFilePickers($container);
		}
		if (!$container.find('textarea.wysiwyg').length) return;
		var tinyMceReady = window.tinymce ? $.Deferred().resolve().promise() : loadWidgetScript(data.assets.tinyMce);
		tinyMceReady.then(function(){
			if (window.tinymce){
				var base = window.location.pathname.replace(/\/admin(?:\/.*)?$/, '').replace(/\/[a-z]{2}(?:-[A-Z]{2})?$/, '');
				window.tinymce.baseURL = base+'/dist/js/tinymce';
			}
			if (window.HiddenCMS && typeof window.HiddenCMS.initTinyMce === 'function') return;
			return loadWidgetScript(data.assets.formTinyMce);
		}).done(function(){
			if (window.HiddenCMS && typeof window.HiddenCMS.initTinyMce === 'function'){
				window.HiddenCMS.initTinyMce($container);
			}
		});
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
		return '<div class="lb-col" data-key="'+col._key+'" style="grid-column:span '+width+'">'+
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
		var inherited = !!zone.inherited;
		var inheritance = data.isBaseOutline ? '' : '<label class="lb-inheritance"><input type="checkbox" class="lb-zone-inherited" '+(inherited ? 'checked' : '')+'> <span><?php echo $this->lang('Inherit from the default outline') ?></span></label>';
		return '<section class="lb-zone'+(inherited ? ' is-inherited' : '')+'" data-zone-index="'+index+'">'+
			'<header class="lb-zone-header"><div><span><?php echo $this->lang('Section') ?></span><h2>'+escapeHtml(zone.title)+'</h2></div>'+
			inheritance+'<button type="button" class="ui small primary button lb-add-row" '+(inherited ? 'disabled' : '')+'><?php echo icon('fas fa-plus').' '.$this->lang('Row') ?></button></header>'+
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
		initPointerSort('.lb-rows', '.lb-row', '.lb-row-drag', 'y');
		initPointerSort('.lb-cols', '.lb-col', '.lb-col-drag', 'x');
		initPointerSort('.lb-widgets', '.lb-widget', '.lb-widget > .lb-drag', 'y');
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
					$col.css('grid-column', 'span '+units);
					$col.find('.lb-col-size-label').text(units+'/12');
					$next.css('grid-column', 'span '+nextUnits).find('.lb-col-size-label').text(nextUnits+'/12');
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

	function initPointerSort(containerSelector, itemSelector, handleSelector, axis){
		$canvas.find(handleSelector).on('pointerdown', function(event){
			if (event.button !== undefined && event.button !== 0) return;
			var $item = $(this).closest(itemSelector);
			var before = clone(state);
			var moved = false;
			if (this.setPointerCapture) this.setPointerCapture(event.pointerId);
			$item.addClass('lb-is-dragging');
			$('body').addClass('lb-is-sorting');
			event.preventDefault();
			event.stopPropagation();

			$(document).on('pointermove.lbSort', function(moveEvent){
				var target = document.elementFromPoint(moveEvent.clientX, moveEvent.clientY);
				var $container = $(target).closest(containerSelector);
				if (!$container.length) return;
				var $target = $(target).closest(itemSelector);
				if ($target.length && !$target.is($item)){
					var rect = $target[0].getBoundingClientRect();
					var after = axis === 'x' ? moveEvent.clientX > rect.left + rect.width / 2 : moveEvent.clientY > rect.top + rect.height / 2;
					$item[after ? 'insertAfter' : 'insertBefore']($target);
					moved = true;
				}
				else if (!$target.length && !$container.children(itemSelector).last().is($item)){
					$container.append($item);
					moved = true;
				}
			}).one('pointerup.lbSort pointercancel.lbSort', function(){
				$(document).off('.lbSort');
				$item.removeClass('lb-is-dragging');
				$('body').removeClass('lb-is-sorting');
				if (moved){
					syncOrder();
					if (serialize(state) !== serialize(before)) commit(before);
					else render();
				}
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
				'<div class="header"><?php echo icon('fas fa-cogs').' '.$this->lang('Widget settings') ?><i class="close icon" role="button" tabindex="0" aria-label="<?php echo $this->lang('Close') ?>"></i></div>',
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
				injectWidgetSettings($settings, html).always(function(){ initializeWidgetEditors($settings); });
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
			$modal.find('#live-editor-settings-form').trigger('nf.live-editor-settings.submit');
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
		$modal.on('click', '.close.icon, .cancel', function(){ $modal.modal('hide'); });
		$modal.on('keydown', '.close.icon', function(event){ if (event.key === 'Enter' || event.key === ' ') $(this).trigger('click'); });
		$modal.modal({
			autofocus: false,
			observeChanges: true,
			onApprove: function(){ applyWidget(); return false; },
			onHidden: function(){
				if (window.tinymce){
					$modal.find('textarea.wysiwyg[id]').each(function(){
						var editor = tinymce.get(this.id);
						if (editor) editor.remove();
					});
				}
				$modal.remove();
			}
		}).modal('show');
		selectWidget(widgetName, true, !!existing);
		showStep('widget');
	}

	function openStyleModal(model, templateSelector, title){
		if ($('.layout-builder-style-modal').length) return;
		var before = clone(state), classes = $.grep(String(model.style || '').split(/\s+/), Boolean);
		var modifiers = {}, $modal = $([ 
			'<div class="ui large modal layout-builder-style-modal">',
				'<div class="header"><?php echo icon('fas fa-paint-brush') ?> '+escapeHtml(title)+'<i class="close icon" role="button" tabindex="0" aria-label="<?php echo $this->lang('Close') ?>"></i></div>',
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
		$modal.on('click', '.close.icon, .cancel', function(){ $modal.modal('hide'); });
		$modal.on('keydown', '.close.icon', function(event){ if (event.key === 'Enter' || event.key === ' ') $(this).trigger('click'); });
		$modal.modal({
			autofocus: false,
			onApprove: function(){ applyStyle(); return false; },
			onHidden: function(){ $modal.remove(); }
		}).modal('show');
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

	function fitToGrid(cols){
		if (!cols.length) return;
		if (cols.length === 1){
			cols[0].size = 'col-12';
			return;
		}

		var weights = $.map(cols, function(col){ return colWidth(col.size); });
		var total = weights.reduce(function(sum, width){ return sum + width; }, 0);
		var remaining = 12;
		$.each(cols, function(index, col){
			var columnsLeft = cols.length - index - 1;
			var width = columnsLeft ? Math.round(weights[index] / total * 12) : remaining;
			width = Math.max(1, Math.min(remaining - columnsLeft, width));
			col.size = 'col-'+width;
			remaining -= width;
		});
	}

	function normalizeGrid(){
		$.each(state, function(zoneIndex, zone){
			$.each(zone.rows || [], function(rowIndex, row){
				var total = (row.cols || []).reduce(function(sum, col){ return sum + colWidth(col.size); }, 0);
				if (total !== 12) fitToGrid(row.cols || []);
			});
		});
	}

	$canvas.on('change', '.lb-zone-inherited', function(){
		var before = clone(state), zone = state[parseInt($(this).closest('.lb-zone').data('zone-index'), 10)];
		zone.inherited = this.checked;
		if (!zone.inherited){
			$.each(zone.rows || [], function(rowIndex, row){
				$.each(row.cols || [], function(colIndex, col){
					$.each(col.widgets || [], function(widgetIndex, widget){
						widget.id = 0;
						widget.dirty = true;
					});
				});
			});
		}
		commit(before);
	});

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
			$.each(lookup.rows, function(key, row){
				var count = row.cols.length;
				row.cols = $.grep(row.cols, function(col){ return col._key !== colKey; });
				if (row.cols.length !== count) fitToGrid(row.cols);
			});
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

	$('.layout-builder-tabs [data-builder-tab]').on('click', function(){
		var tab = $(this).data('builder-tab');
		$('.layout-builder-tabs [data-builder-tab]').removeClass('active');
		$(this).addClass('active');
		$('[data-builder-panel]').prop('hidden', true).filter('[data-builder-panel="'+tab+'"]').prop('hidden', false);
	});

	function saveOutlineForm($button, url, $form, $message){
		var $status = $($message), values = $form.serializeArray();
		values.push({name: 'outline_id', value: data.outlineId});
		$button.addClass('loading').prop('disabled', true);
		$status.removeClass('success error').text('');
		$.post(url, $.param(values)).done(function(response){
			if (typeof response === 'string'){
				try { response = JSON.parse(response); } catch (error){ response = null; }
			}
			if (!response || !response.ok){
				$status.addClass('error').text('<?php echo $this->lang('Unable to save changes') ?>');
				return;
			}
			$status.addClass('success').text(response.message || '<?php echo $this->lang('Changes saved') ?>');
			if (response.reload) window.setTimeout(function(){ window.location.href = response.reload; }, 450);
		}).fail(function(xhr){
			var response = xhr.responseJSON || {};
			$status.addClass('error').text(response.error || '<?php echo $this->lang('Unable to save changes') ?>');
		}).always(function(){ $button.removeClass('loading').prop('disabled', false); });
	}

	$('#layout-builder-assignments-save').on('click', function(){
		saveOutlineForm($(this), data.urls.assignmentsSave, $('#layout-builder-assignments'), '#layout-builder-assignments-status');
	});
	$('#layout-builder-options-save').on('click', function(){
		saveOutlineForm($(this), data.urls.optionsSave, $('#layout-builder-options'), '#layout-builder-options-status');
	});

	window.addEventListener('beforeunload', function(event){
		if (serialize(state) !== savedState){ event.preventDefault(); event.returnValue = ''; }
	});

	normalizeGrid();
	render();
})(jQuery);
