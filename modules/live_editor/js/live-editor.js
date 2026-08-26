var modal_style = function(title, $element, styles, callback){
	if ($('body').find('.live-editor-modal').length){
		return;
	}

	var $modal = $('\
		<div class="ui large modal live-editor-modal" role="dialog">\
			<div class="header"><?php echo icon('fas fa-paint-brush') ?> '+title+'<i class="close icon" aria-label="<?php echo $this->lang('Fermer') ?>"></i></div>\
			<div class="content">'+$(styles).html()+'</div>\
			<div class="actions">\
				<button type="button" class="ui button cancel"><?php echo $this->lang('Annuler') ?></button>\
				<button type="button" class="ui primary button live-editor-confirm"><?php echo $this->lang('Valider') ?></button>\
			</div>\
		</div>').appendTo('body').data('element', $element);

	var $widget = $element.parents('.live-editor-widget:first');
	var original_style = $widget.length ? ($widget.data('widget-style') || '') : ($element.data('original-style') || '');
	var accepted = false;

	$element.data('previous-style', original_style);

	$modal.find('[data-style]').each(function(){
		if ($(this).data('style') == original_style){
			$(this).addClass('active');
			return false;
		}
	});

	$modal.modal({
		autofocus: false,
		onHidden: function(){
			if (!accepted && $element.data('previous-style') != original_style){
				$element.switchClass($element.data('previous-style'), original_style, 200);
			}
			$modal.remove();
		}
	}).modal('show');

	$modal.find('.live-editor-confirm').on('click', function(){
		var style = $element.data('previous-style');
		accepted = true;

		if ($widget.length){
			$widget.data('widget-style', style);
		}
		else {
			$element.data('original-style', style);
		}

		$modal.modal('hide');
		callback(style);
	});
};

var modal_settings = function(title, settings, callback){
	if ($('body').find('.live-editor-modal').length){
		return;
	}

	var settings_request = 0;
	var settings_key = null;

	var load_settings = function(){
		var widget = $('#live-editor-settings-widget').val();
		var type   = $('#live-editor-settings-type').val();
		var key    = widget+'::'+type;

		if (settings_key == key){
			return;
		}

		settings_key = key;

		if ($('#live-editor-settings').data('widget-id') && $('#live-editor-settings').data('original-widget') == widget && $('#live-editor-settings').data('original-type') == type){
			var data = {
				widget_id: $('#live-editor-settings').data('widget-id')
			};
		}
		else {
			var data = {
				widget: widget,
				type: type
			};
		}

		var request = ++settings_request;
		$('#live-editor-settings').html('');
		set_step_available('settings', false);

		$.post('<?php echo url('admin/ajax/live-editor/widget-admin') ?>', data, function(data){
			if (request == settings_request){
				var has_settings = $.trim(data || '') != '';

				$('#live-editor-settings').html(has_settings ? data : '');
				set_step_available('settings', has_settings);
				show_step(current_step);
			}
		});
	};

	var $modal = $('\
		<div class="ui large modal live-editor-modal" role="dialog">\
			<div class="header"><?php echo icon('fas fa-cogs') ?> '+title+'<i class="close icon" aria-label="<?php echo $this->lang('Fermer') ?>"></i></div>\
			<div class="content">'+settings+'</div>\
			<div class="actions">\
				<button type="button" class="ui button cancel"><?php echo $this->lang('Annuler') ?></button>\
				<button type="button" class="ui button live-editor-previous"><?php echo icon('fas fa-chevron-left').' '.$this->lang('Précédent') ?></button>\
				<button type="button" class="ui primary button live-editor-next"><?php echo $this->lang('Suivant').' '.icon('fas fa-chevron-right') ?></button>\
				<button type="button" class="ui primary button live-editor-confirm"><?php echo $this->lang('Valider') ?></button>\
			</div>\
		</div>').appendTo('body');

	var step_order = ['widget', 'type', 'title', 'settings'];
	var current_step = 'widget';
	var selected_widget = $modal.find('#live-editor-settings-widget').val();

	var available_steps = function(){
		return $.grep(step_order, function(step){
			return $modal.find('.live-editor-settings-panel[data-step="'+step+'"]').data('available') !== false;
		});
	};

	var set_step_available = function(step, available){
		$modal.find('.live-editor-settings-panel[data-step="'+step+'"]').data('available', available);
		$modal.find('.live-editor-settings-steps .step[data-step="'+step+'"]').toggleClass('disabled', !available);
	};

	var show_step = function(step){
		var steps = available_steps();
		var index = $.inArray(step, steps);
		var previous_index = $.inArray(current_step, steps);

		if (index == -1){
			index = 0;
			step = steps[index];
		}

		var $panels = $modal.find('.live-editor-settings-panels');
		var $current_panel = $panels.find('.live-editor-settings-panel:visible').first();
		var $next_panel = $panels.find('.live-editor-settings-panel[data-step="'+step+'"]');

		if (!$current_panel.length){
			$next_panel.show();
		}
		else if (!$current_panel.is($next_panel)){
			var current_height = $current_panel.outerHeight(true);
			var direction = index >= previous_index ? 1 : -1;

			$next_panel.css({
				display: 'block',
				left: 0,
				opacity: 0,
				position: 'absolute',
				top: 0,
				transform: 'translateX('+(direction * 12)+'px)',
				width: '100%'
			});

			var next_height = $next_panel.outerHeight(true);

			$current_panel.css({
				left: 0,
				position: 'absolute',
				top: 0,
				width: '100%'
			});
			$panels.css('height', current_height).stop(true).animate({height: next_height}, 220);

			window.requestAnimationFrame(function(){
				$current_panel.css({opacity: 0, transform: 'translateX('+(-direction * 8)+'px)'});
				$next_panel.css({opacity: 1, transform: 'translateX(0)'});
			});

			window.setTimeout(function(){
				$current_panel.hide().removeAttr('style');
				$next_panel.css({position: 'relative', top: 'auto', left: 'auto', width: 'auto'});
				$panels.css('height', 'auto');
			}, 230);
		}

		current_step = step;
		$modal.find('.live-editor-settings-steps .step').removeClass('active completed');
		$.each(steps, function(i, name){
			var $step = $modal.find('.live-editor-settings-steps .step[data-step="'+name+'"]');

			if (i < index){
				$step.addClass('completed');
			}
			else if (i == index){
				$step.addClass('active');
			}
		});

		$modal.find('.live-editor-previous').toggle(index > 0);
		$modal.find('.live-editor-next').toggle(index < steps.length - 1);
		$modal.find('.live-editor-confirm').toggle(index == steps.length - 1);
	};

	set_step_available('widget', true);
	set_step_available('type', false);
	set_step_available('title', true);
	set_step_available('settings', false);

	$modal.on('change', '#live-editor-settings-widget', function(){
		var $widgets = $(this);
		var $widget_card = $modal.find('.live-editor-widget-card[data-widget="'+$widgets.val()+'"]');
		var $types = $modal.find('#live-editor-settings-type');
		var $type_cards = $modal.find('.live-editor-type-card');
		var $available_types = $type_cards.filter('[data-widget="'+$widgets.val()+'"]');
		var $selected_type = $available_types.filter('[data-type="'+$types.val()+'"]');
		var widget_changed = selected_widget != null && selected_widget != $widgets.val();

		$modal.find('.live-editor-widget-card').removeClass('active').attr('aria-selected', 'false');
		$widget_card.addClass('active').attr('aria-selected', 'true');
		$modal.find('.live-editor-settings-choice-icon').html($widget_card.find('.live-editor-widget-card-icon').html());
		$modal.find('.live-editor-settings-choice-name').text($widget_card.find('.header').text());

		$type_cards.hide().removeClass('active').attr('aria-selected', 'false');
		$available_types.show();

		if ($available_types.length){
			if (!$selected_type.length || widget_changed){
				$selected_type = $available_types.first();
			}

			$types.val($selected_type.data('type'));
			$selected_type.addClass('active').attr('aria-selected', 'true');
			set_step_available('type', true);
		}
		else {
			$types.val('index');
			set_step_available('type', false);
		}

		if (widget_changed){
			$modal.find('#live-editor-settings-title').val('').removeData('value');
			$modal.find('.live-editor-display-title').checkbox('set checked');
		}

		if ($(this).val() == 'module'){
			$modal.find('#live-editor-settings-title').val('');
			set_step_available('title', false);
		}
		else {
			set_step_available('title', true);
		}

		selected_widget = $widgets.val();
		show_step(current_step);
		load_settings();
	});

	$modal.on('click', '.live-editor-widget-card', function(){
		var widget = $(this).data('widget');

		if ($modal.find('#live-editor-settings-widget').val() != widget){
			$modal.find('#live-editor-settings-widget').val(widget).trigger('change');
		}
	});

	$modal.on('click', '.live-editor-type-card', function(){
		var type = $(this).data('type');

		if ($modal.find('#live-editor-settings-type').val() != type){
			$modal.find('.live-editor-type-card').removeClass('active').attr('aria-selected', 'false');
			$(this).addClass('active').attr('aria-selected', 'true');
			$modal.find('#live-editor-settings-type').val(type);
			load_settings();
		}
	});

	$modal.on('click', '.live-editor-previous, .live-editor-next', function(){
		var steps = available_steps();
		var index = $.inArray(current_step, steps);
		var direction = $(this).hasClass('live-editor-next') ? 1 : -1;

		show_step(steps[index + direction]);
	});

	$modal.on('click', '.live-editor-settings-steps .completed.step', function(){
		show_step($(this).data('step'));
	});

	$modal.find('#live-editor-settings-form').submit(function(){
		if ($modal.find('.live-editor-next:visible').length){
			$modal.find('.live-editor-next').trigger('click');
		}
		else {
			$modal.find('.live-editor-confirm').trigger('click');
		}

		return false;
	});

	$modal.find('.ui.dropdown').dropdown();
	$modal.find('.live-editor-display-title').checkbox();
	$('#live-editor-settings-widget').trigger('change');
	show_step('widget');
	$modal.modal({
		autofocus: false,
		observeChanges: true,
		onHidden: function(){
			$modal.remove();
		}
	}).modal('show');

	$modal.find('.live-editor-confirm').on('click', function(){
		$('#live-editor-settings-form').trigger('nf.live-editor-settings.submit');

		$modal.modal('hide');

		var settings = {};

		settings.settings = null;

		(new FormData($('#live-editor-settings-form')[0])).forEach(function(value, name){
			if (settings[name] !== undefined){
				if (!settings[name].push){
					settings[name] = [settings[name]];
				}

				settings[name].push(value || '');
			}
			else {
				settings[name] = value || '';
			}
		});

		if (typeof settings.title == 'undefined'){
			settings.title = '';
		}

		settings.display_title = $modal.find('.live-editor-display-title').checkbox('is checked') ? 1 : 0;

		callback(settings);
	});
};

var modal_fork = function(callback){
	if ($('body').find('.live-editor-modal').length){
		return;
	}

	var $modal = $('\
		<div class="ui small modal live-editor-modal" role="dialog">\
			<div class="header"><?php echo $this->lang('Revenir à la disposition commune') ?><i class="close icon" aria-label="<?php echo $this->lang('Fermer') ?>"></i></div>\
			<div class="content"><?php echo $this->lang('Êtes-vous sûr(e) de vouloir revenir à la disposition commune ?<br />Toutes les <b>colonnes</b> et <b>widgets</b> associés à cette zone seront perdus.') ?></div>\
			<div class="actions">\
				<button type="button" class="ui button cancel"><?php echo $this->lang('Annuler') ?></button>\
				<button type="button" class="ui negative button live-editor-confirm"><?php echo $this->lang('Continuer') ?></button>\
			</div>\
		</div>').appendTo('body');

	$modal.modal({
		autofocus: false,
		onHidden: function(){
			$modal.remove();
		}
	}).modal('show');

	$modal.find('.live-editor-confirm').on('click', function(){
		$modal.modal('hide');
		callback();
	});
};

var modal_delete = function(message, callback){
	if ($('body').find('.live-editor-modal').length){
		return;
	}

	var $modal = $('\
		<div class="ui small modal live-editor-modal" role="dialog">\
			<div class="header"><?php echo icon('far fa-trash-alt').' '.$this->lang('Confirmation de suppression') ?><i class="close icon" aria-label="<?php echo $this->lang('Fermer') ?>"></i></div>\
			<div class="content">'+message+'</div>\
			<div class="actions">\
				<button type="button" class="ui button cancel"><?php echo $this->lang('Annuler') ?></button>\
				<button type="button" class="ui negative button live-editor-confirm"><?php echo icon('far fa-trash-alt') ?> <?php echo $this->lang('Supprimer') ?></button>\
			</div>\
		</div>').appendTo('body');

	$modal.modal({
		autofocus: false,
		onHidden: function(){
			$modal.remove();
		}
	}).modal('show');

	$modal.find('.live-editor-confirm').on('click', function(){
		$modal.modal('hide');
		callback();
	});
};

$(function(){
	var $widgets = $('[data-mode="<?php echo \HB\HiddenCMS\Core\Output::WIDGETS ?>"]');
	var $screen_picker = $('.live-editor-screen-picker');

	if ($.fn.dropdown){
		$('.live-editor-navbar .ui.dropdown').dropdown({
			action: 'nothing'
		});
	}

	$('form[target="live-editor-iframe"]').submit();

	$('.live-editor-screen[data-width]').click(function(){
		var width = $(this).data('width');

		if (width == '100%'){
			var size = '20px';
			width = 'calc('+width+' - 40px)';
		}
		else {
			var size = 'calc(50% - '+width+' / 2)';
		}

		$('.live-editor-iframe').width(width).css('left', size);
		$('.live-editor-screen[data-width]').removeClass('active');
		$(this).addClass('active');
		$('.live-editor-screen-current').html($(this).html());

		if ($.fn.dropdown){
			$screen_picker.dropdown('hide');
		}
	});

	$('.live-editor-mode').click(function(){
		$(this).toggleClass('active');

		if ($(this).data('mode') == <?php echo \HB\HiddenCMS\Core\Output::WIDGETS ?>){
			return;
		}

		var mode = <?php echo $this->output->live_editor() ?>;
		$('.live-editor-mode.active').each(function(){
			mode += $(this).data('mode');
		});

		$('input[type="hidden"][name="live_editor"]').val(mode);
		$('form[target="live-editor-iframe"]').submit();
	});

	/* Styles Overview */
	$('body').on('click', '.live-editor-overview:not(.active)', function(){
		var $element = $(this).parents('.modal:first').data('element');
		$element.switchClass($element.data('previous-style'), $(this).data('style'), 200);
		$element.data('previous-style', $(this).data('style'));
		$('.live-editor-overview').removeClass('active');
		$(this).addClass('active');
	});

	$('.live-editor-iframe iframe').on('load', function(){
		var $iframe = $(this).contents();

		$('#live-editor-map').html($('#live-editor-map').data('outline-title') || $iframe.find('#live_editor').data('module-title'));

		$iframe.on('mouseover', '.widget, .module', function(){
			if ($widgets.hasClass('active') && !$(this).find('.widget-hover').length){
				$iframe.find('.widget-hover').remove();
				$('	<div class="widget-hover">\
						<div class="widget-hover-content">\
							<h5>'+($(this).hasClass('module') ? '<b><?php echo $this->lang('Module') ?></b> ' : '')+$(this).data('title')+'</h5>\
							<div class="btn-group" role="group">\
								'+(!$(this).hasClass('module') ? '<button type="button" class="btn btn-info live-editor-style" data-toggle="tooltip" data-container="body" data-placement="bottom" title="<?php echo $this->lang('Apparence') ?>"><?php echo icon('fas fa-paint-brush') ?></button>' : '')+'\
								<button type="button" class="btn btn-warning live-editor-setting" data-toggle="tooltip" data-placement="bottom" data-container="body" title="<?php echo $this->lang('Configurer') ?>"><?php echo icon('fas fa-cogs') ?></button>\
								<button type="button" class="btn btn-danger live-editor-delete" data-toggle="tooltip" data-placement="bottom" data-container="body" title="<?php echo $this->lang('Supprimer') ?>"><?php echo icon('far fa-trash-alt') ?></button>\
							</div>\
						</div>\
					</div>').prependTo(this).fadeTo('fast', 1);
			}
		});

		$iframe.on('mouseleave', '.widget-hover', function(){
			$(this).remove();
		});

		$iframe.on('click', 'a', function(){
			if ($('#live-editor-map').data('outline-mode')){
				return false;
			}

			var href = $(this).attr('href');

			if (href.match(/<?php echo str_replace('/', '\/', url()) ?>(?!(admin|live-editor|#))/)){
				$('#live-editor-map').html('<?php echo icon('fas fa-spinner fa-spin').' '.$this->lang('Chargement en cours...') ?>');
				$('form[target="live-editor-iframe"]').prop('action', href).submit();
			}

			return false;
		});

		/* Zone Fork */
		$iframe.on('click', '.live-editor-zone .live-editor-fork', function(){
			var $this = $(this);
			var fork = function(){
				$('.live-editor-save').show();

				var $zone = $this.parents('[data-disposition-id]:first');

				$.post('<?php echo url('admin/ajax/live-editor/zone-fork') ?>', {
					disposition_id: $zone.data('disposition-id'),
					url: $iframe[0].location.pathname,
					live_editor: $('input[type="hidden"][name="live_editor"]').val()
				}, function(data){
					if ($(data).find('.live-editor-widget.module').length){
						$('form[target="live-editor-iframe"]').submit();
					}
					else {
						$zone.replaceWith(data);
					}
				}).always(function(){
					$('.live-editor-save').hide();
				});
			};

			if ($this.data('enabled')){
				modal_fork(fork);
			}
			else {
				fork();
			}
		});

		/* Row Add */
		$iframe.on('click', '.live-editor-add-row', function(){
			var $this = $(this).parents('[data-disposition-id]:first');
			$('.live-editor-save').show();

			$.post('<?php echo url('admin/ajax/live-editor/row-add') ?>', {
				disposition_id: $this.data('disposition-id'),
				live_editor: $('input[type="hidden"][name="live_editor"]').val()
			}, function(data){
				var $rows_button = $('.live-editor-mode[data-mode="<?php echo \HB\HiddenCMS\Core\Output::ROWS ?>"]');

				if (!$rows_button.hasClass('active')){
					$rows_button.trigger('click');
				}
				else {
					$this.append(data);
				}
			}).always(function(){
				$('.live-editor-save').hide();
			});
		});

		/* Row Move */
		$iframe.find('[data-disposition-id]').sortable({
			axis: 'y',
			containment: 'parent',
			cursor: 'move',
			intersect: 'pointer',
			items: '> .live-editor-row',
			opacity: 0.6,
			placeholder: 'live-editor-placeholder',
			revert: true,
			start: function(event, ui){
				ui.placeholder.css('height', ui.item.height());
			},
			update: function(event, ui){
				$('.live-editor-save').show();

				$.post('<?php echo url('admin/ajax/live-editor/row-move') ?>', {
					disposition_id: $(this).data('disposition-id'),
					row_id: ui.item.find('.row:first').data('row-id'),
					position: $(this).find('.live-editor-row').index(ui.item)
				}).always(function(){
					$('.live-editor-save').hide();
				});
			}
		});

		/* Row Style */
		$iframe.on('click', '.live-editor-row-header .live-editor-style', function(){
			var $this = $(this);
			var $row = $this.parents('.live-editor-row-header:first').next('.row');

			modal_style('<?php echo $this->lang('Apparence de la ligne') ?>', $row, '.live-editor-styles-row', function(style){
				$('.live-editor-save').show();

				$.post('<?php echo url('admin/ajax/live-editor/row-style') ?>', {
					disposition_id: $this.parents('[data-disposition-id]:first').data('disposition-id'),
					row_id: $row.data('row-id'),
					style: style
				}).always(function(){
					$('.live-editor-save').hide();
				});
			});
		});

		/* Row Delete */
		$iframe.on('click', '.live-editor-row-header .live-editor-delete', function(){
			var $this = $(this);

			modal_delete('<?php echo $this->lang('Êtes-vous sûr(e) de vouloir supprimer cette <b>ligne</b> ?<br />Toutes les <b>colonnes</b> et <b>widgets</b> contenus seront également supprimés.') ?>', function(){
				var $row = $this.parents('.live-editor-row-header:first').next('.row');

				$('.live-editor-save').show();

				$.post('<?php echo url('admin/ajax/live-editor/row-delete') ?>', {
					disposition_id: $this.parents('[data-disposition-id]:first').data('disposition-id'),
					row_id: $row.data('row-id')
				}, function(){
					$row.parents('.live-editor-row:first').remove();
				}).always(function(){
					$('.live-editor-save').hide();
				});
			});
		});

		/* Col Add */
		$iframe.on('click', '.live-editor-add-col', function(){
			var $row = $(this).parents('.live-editor-row-header:first').next('[data-row-id]:first');
			$('.live-editor-save').show();

			$.post('<?php echo url('admin/ajax/live-editor/col-add') ?>', {
				disposition_id: $(this).parents('[data-disposition-id]:first').data('disposition-id'),
				row_id: $row.data('row-id'),
				live_editor: $('input[type="hidden"][name="live_editor"]').val()
			}, function(data){
				var $cols_button = $('.live-editor-mode[data-mode="<?php echo \HB\HiddenCMS\Core\Output::COLS ?>"]');

				if (!$cols_button.hasClass('active')){
					$cols_button.trigger('click');
				}
				else {
					$row.append(data);
				}
			}).always(function(){
				$('.live-editor-save').hide();
			});
		});

		/* Col Move */
		$iframe.find('[data-row-id]').sortable({
			axis: 'x',
			containment: 'parent',
			cursor: 'move',
			intersect: 'pointer',
			items: '> [data-col-id]',
			opacity: 0.6,
			placeholder: 'live-editor-placeholder',
			revert: true,
			start: function(event, ui){
				if (match = ui.item.prop('class').match(/(col-\d{1,2})/)){
					ui.placeholder.addClass(match[1]);
					ui.placeholder.css('height', ui.item.height());
				}
			},
			update: function(event, ui){
				$('.live-editor-save').show();

				$.post('<?php echo url('admin/ajax/live-editor/col-move') ?>', {
					disposition_id: $(this).parents('[data-disposition-id]:first').data('disposition-id'),
					row_id: $(this).data('row-id'),
					col_id: ui.item.data('col-id'),
					position: $(this).find('[data-col-id]').index(ui.item)
				}).always(function(){
					$('.live-editor-save').hide();
				});
			}
		});

		/* Col Size */
		$iframe.on('click', '.live-editor-col .live-editor-size', function(){
			var $col = $(this).parents('[data-col-id]:first');
			var size;
			var old_size = size = 12;

			if (match = $col.prop('class').match(/col-lg-(\d{1,2})/)){
				old_size = parseInt(match[1]);
			}

			size = Math.max(1, Math.min(12, old_size+parseInt($(this).data('size'))));

			if (size != old_size){
				$col.switchClass('col-lg-'+old_size, 'col-lg-'+size, 200);

				$('.live-editor-save').show();

				$.post('<?php echo url('admin/ajax/live-editor/col-size') ?>', {
					disposition_id: $(this).parents('[data-disposition-id]:first').data('disposition-id'),
					row_id: $(this).parents('[data-row-id]:first').data('row-id'),
					col_id: $col.data('col-id'),
					size: size
				}).always(function(){
					$('.live-editor-save').hide();
				});
			}
		});

		/* Col Delete */
		$iframe.on('click', '.live-editor-col > .btn-group > .live-editor-delete', function(){
			var $this = $(this);
			var $col  = $(this).parents('[data-col-id]:first');

			modal_delete('<?php echo $this->lang('Êtes-vous sûr(e) de vouloir supprimer cette <b>colonne</b> ?<br />Tous les <b>widgets</b> contenus seront également supprimés.') ?>', function(){
				$('.live-editor-save').show();

				$.post('<?php echo url('admin/ajax/live-editor/col-delete') ?>', {
					disposition_id: $this.parents('[data-disposition-id]:first').data('disposition-id'),
					row_id: $this.parents('[data-row-id]:first').data('row-id'),
					col_id: $col.data('col-id')
				}, function(){
					$col.remove();
				}).always(function(){
					$('.live-editor-save').hide();
				});
			});
		});

		/* Widget Add */
		$iframe.on('click', '.live-editor-add-widget', function(){
			var $col  = $(this).parents('[data-col-id]:first');
			var data    = {
				disposition_id: $(this).parents('[data-disposition-id]:first').data('disposition-id'),
				row_id: $(this).parents('[data-row-id]:first').data('row-id'),
				col_id: $col.data('col-id'),
				widget_id: -1
			};

			$.post('<?php echo url('admin/ajax/live-editor/widget-settings') ?>', data, function(html){
				modal_settings('<?php echo $this->lang('Nouveau Widget') ?>', html, function(settings){
					$.extend(data, settings);
					$.extend(data, {
						live_editor: $('input[type="hidden"][name="live_editor"]').val()
					});

					$('.live-editor-save').show();

					$.post('<?php echo url('admin/ajax/live-editor/widget-add') ?>', data, function(data){
						if (settings.widget == 'module'){
							$('form[target="live-editor-iframe"]').submit();
						}
						else {
							$col.find('.live-editor-col:first').append(data);
						}
					}).always(function(){
						$('.live-editor-save').hide();
					});
				});
			});
		});

		/* Widget Move */
		$iframe.find('[data-col-id]').sortable({
			axis: 'y',
			containment: 'parent',
			cursor: 'move',
			intersect: 'pointer',
			items: '[data-widget-id]',
			opacity: 0.6,
			placeholder: 'live-editor-placeholder',
			revert: true,
			start: function(event, ui){
				ui.placeholder.css('height', ui.item.height());
			},
			update: function(event, ui){
				$('.live-editor-save').show();

				$.post('<?php echo url('admin/ajax/live-editor/widget-move') ?>', {
					disposition_id: $(this).parents('[data-disposition-id]:first').data('disposition-id'),
					row_id: $(this).parents('[data-row-id]:first').data('row-id'),
					col_id: $(this).data('col-id'),
					widget_id: ui.item.data('widget-id'),
					position: $(this).find('[data-widget-id]').index(ui.item)
				}).always(function(){
					$('.live-editor-save').hide();
				});
			}
		});

		/* Widget Style */
		$iframe.on('click', '.live-editor-widget .live-editor-style', function(){
			var $widget = $(this).parents('[data-widget-id]:first');
			var data    = {
				disposition_id: $(this).parents('[data-disposition-id]:first').data('disposition-id'),
				row_id: $(this).parents('[data-row-id]:first').data('row-id'),
				col_id: $(this).parents('[data-col-id]:first').data('col-id'),
				widget_id: $widget.data('widget-id')
			};

			modal_style('<?php echo $this->lang('Apparence du Widget') ?>', $widget.children('.card'), '.live-editor-styles-widget', function(style){
				$.extend(data, {
					style: style
				});

				$('.live-editor-save').show();

				$.post('<?php echo url('admin/ajax/live-editor/widget-style') ?>', data).always(function(){
					$('.live-editor-save').hide();
				});
			});
		});

		/* Widget Settings */
		$iframe.on('click', '.live-editor-widget .live-editor-setting', function(){
			var $widget = $(this).parents('[data-widget-id]:first');
			var data    = {
				disposition_id: $(this).parents('[data-disposition-id]:first').data('disposition-id'),
				row_id: $(this).parents('[data-row-id]:first').data('row-id'),
				col_id: $(this).parents('[data-col-id]:first').data('col-id'),
				widget_id: $widget.data('widget-id')
			};

			$.post('<?php echo url('admin/ajax/live-editor/widget-settings') ?>', data, function(html){
				modal_settings('<?php echo $this->lang('Configuration du Widget') ?>', html, function(settings){
					$.extend(data, settings);

					$('.live-editor-save').show();

					$.post('<?php echo url('admin/ajax/live-editor/widget-update') ?>', data, function(data){
						if (settings.widget == 'module'){
							$('form[target="live-editor-iframe"]').submit();
						}
						else {
							$widget.replaceWith(data);
						}
					}).always(function(){
						$('.live-editor-save').hide();
					});
				});
			});
		});

		/* Widget Delete */
		$iframe.on('click', '.live-editor-widget .live-editor-delete', function(){
			var $this = $(this);
			var $widget = $this.parents('[data-widget-id]:first');

			//data doit être construit avant l'appel à la modal
			var data  = {
				disposition_id: $this.parents('[data-disposition-id]:first').data('disposition-id'),
				row_id: $this.parents('[data-row-id]:first').data('row-id'),
				col_id: $this.parents('[data-col-id]:first').data('col-id'),
				widget_id: $widget.data('widget-id')
			};

			modal_delete('<?php echo $this->lang('Êtes-vous sûr(e) de vouloir supprimer ce <b>widget</b> ?') ?>', function(){
				$('.live-editor-save').show();

				$.post('<?php echo url('admin/ajax/live-editor/widget-delete') ?>', data, function(){
					$widget.remove();
					$('.live-editor-save').hide();
				});
			});
		});
	});
});

$('[data-typer]').attr('data-typer', function(i, txt){
	var $typer = $(this),
		tot = txt.length,
		pauseMax = 300,
		pauseMin = 60,
		ch = 0;

	(function typeIt() {
		if (ch > tot) return;
		$typer.text(txt.substring(0, ch++));
		setTimeout(typeIt, ~~(Math.random() * (pauseMax - pauseMin + 1) + pauseMin));
	}());
});


