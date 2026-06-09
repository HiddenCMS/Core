$(function(){
	var access_context = function($element){
		var $context = $element.closest('.module-access');
		return $context.length ? $context : $(document);
	};

	var access_data = function($context){
		return {
			'module': $context.find('[name="module"]:first').val(),
			'type':   $context.find('[name="type"]:first').val(),
			'id':     $context.find('[name="id"]:first').val()
		};
	};

	var access_row = function($context){
		return $context.find('> .row:first');
	};

	var access_columns = function($context){
		return access_row($context).children('[class^="col-"], [class*=" col-"]');
	};

	var init_access = function($root){
		$root.find('.module-access').addBack('.module-access').each(function(){
			var $context = $(this);

			if ($context.data('accessLoaded')){
				return;
			}

			$context.data('accessLoaded', true);
			$context.find('[data-action]:first').trigger('click');
		});
	};

	var update_radio = function($btn){
		var color = $btn.data('class');

		if (!$btn.hasClass(color)){
			$btn.addClass(color).find('i').addClass('text-'+color).removeClass('text-muted').removeClass('fas fa-toggle-off').addClass('fas fa-toggle-on');
			$btn.parent().find('.access-radio').each(function(){
				if ($(this)[0] != $btn[0]){
					var color = $(this).data('class')
					$(this).removeClass(color).find('i').removeClass('text-'+color).addClass('text-muted').removeClass('fas fa-toggle-on').addClass('fas fa-toggle-off');
				}
			});

			return true;
		}

		return false;
	};

	var update_access = function($btn, revoke){
		var $context = access_context($btn);
		var data = access_data($context);

		data['action'] = $context.find('.info[data-action]:first').data('action');

		var $table = $btn.parents('.table:first');
		var $tr = $btn.parents('tr:first');

		if ($table.find('[data-group]').length){
			data['groups'] = {};

			$table.find('[data-group]').each(function(){
				data['groups'][$(this).data('group')] = $(this).find('.access-radio.success').length;
			});
		}
		else {
			data['user'] = {};
			data['user'][$tr.find('[data-user-id]').data('user-id')] = typeof revoke == 'undefined' ? $tr.find('.access-radio.success').length : -1;
		}

		$.ajax({
			url: '<?php echo url('admin/ajax/access/update.json') ?>',
			type: 'POST',
			data: data,
			success: function(data){
				var $cols = access_columns($context);

				if (typeof data.details != 'undefined'){
					if ($cols.length > 1){
						$cols.last().remove();
					}

					access_row($context).append(data.details);
				}

				if (typeof data.user_authorized != 'undefined' && typeof data.user_forced != 'undefined'){
					if (data.user_forced){
						$tr.find('.access-status').html('<a class="access-revoke" href="#" data-toggle="tooltip" title="<?php echo $this->lang('Remettre en automatique') ?>"><?php echo icon('fas fa-thumbtack') ?></a>');
					}
					else {
						$tr.find('.access-status').html('');
					}

					update_radio($tr.find('[data-class="'+(data.user_authorized ? 'success' : 'danger')+'"]'));
				}

				access_columns($context).find('.info[data-action] .access-count').html(data.count);
			}
		});
	};

	$(document).on('click', '.module-access [data-action]', function(){
		var $access = $(this).closest('[data-action]');
		var $context = access_context($access);

		if (!$access.hasClass('info')){
			var data = access_data($context);

			data['action'] = $access.data('action');

			$.ajax({
				url: '<?php echo url('admin/ajax/access') ?>',
				type: 'POST',
				data: data,
				success: function(data){
					var $cols = access_columns($context);

					if ($cols.length > 1){
						$cols.last().remove();
					}

					$cols.find('.info[data-action]').removeClass('info');

					$access.addClass('info');
					access_row($context).append(data);
				}
			});
		}

		return false;
	});

	$(document).on('click', '.access-radio', function(){
		if (update_radio($(this))){
			update_access($(this));
		}

		return false;
	});

	$(document).on('click', '.access-revoke', function(){
		update_access($(this), true);

		return false;
	});

	$(document).on('click', '[data-radio]', function(){
		var update = false;
		var $table = $(this).parents('.table:first');

		$table.find('.access-radio[data-class="'+$(this).data('radio')+'"]').each(function(){
			update = update_radio($(this)) || update;
		});

		if (update){
			update_access($(this));
		}
	});

	$(document).on('click', '.access-users', function(){
		var $context = access_context($(this));
		var data = access_data($context);

		data['action'] = $context.find('.info[data-action]:first').data('action');

		$.ajax({
			url: '<?php echo url('admin/ajax/access/users') ?>',
			type: 'POST',
			data: data,
			success: function(data){
				$(data).appendTo('body').modal().on('hidden.bs.modal', function(){
					$(this).remove();
				});
			}
		});

		return false;
	});

	$(document).on('click', '.access-reset', function(){
		$('	<div class="modal modal-access-reset fade" tabindex="-1" role="dialog">\
				<div class="modal-dialog">\
					<div class="modal-content">\
						<div class="modal-header">\
							<h5 class="modal-title"><?php echo $this->lang('Confirmation de réinitialisation des permissions') ?></h5>\
							<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only"><?php echo $this->lang('Fermer') ?></span></button>\
						</div>\
						<div class="modal-body">\
							<?php echo $this->lang('Êtes-vous sûr(e) de vouloir réinitialiser les permissions ?') ?>\
						</div>\
						<div class="modal-footer">\
							<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo $this->lang('Annuler') ?></button>\
							<button class="btn btn-info" data-module="'+$(this).data('module')+'" data-type="'+$(this).data('type')+'" data-id="'+$(this).data('id')+'"><?php echo $this->lang('Réinitialiser') ?></button>\
						</div>\
					</div>\
				</div>\
			</div>').appendTo('body').modal();

		return false;
	});

	$(document).on('click', '.modal-access-reset .btn-info', function(){
		$.ajax({
			url: '<?php echo url('admin/ajax/access/reset') ?>',
			type: 'POST',
			data: {
				'module': $(this).data('module'),
				'type':   $(this).data('type'),
				'id':     $(this).data('id')
			},
			success: function(data){
				window.location.reload();
			}
		});

		return false;
	});

	$(document).on('nf.load', function(){
		init_access($(document));
	});

	init_access($(document));
});
