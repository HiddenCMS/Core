$(function(){
	var status = $('.maintenance-status');
	var toggle = status.find('.maintenance-toggle');
	var input = toggle.find('input');
	var changing = false;
	var labels = {
		open: <?php echo json_encode((string)$this->lang('Site open')) ?>,
		closed: <?php echo json_encode((string)$this->lang('Maintenance enabled')) ?>,
		openDescription: <?php echo json_encode((string)$this->lang('The site is accessible to visitors.')) ?>,
		closedDescription: <?php echo json_encode((string)$this->lang('Visitors can see the maintenance page.')) ?>,
		closeTitle: <?php echo json_encode((string)$this->lang('Enable maintenance mode')) ?>,
		openTitle: <?php echo json_encode((string)$this->lang('Reopen the site')) ?>,
		closeQuestion: <?php echo json_encode((string)$this->lang('Visitors will see the maintenance page. Do you want to continue?')) ?>,
		openQuestion: <?php echo json_encode((string)$this->lang('The site will immediately become accessible to visitors again. Do you want to continue?')) ?>,
		cancel: <?php echo json_encode((string)$this->lang('Cancel')) ?>,
		confirm: <?php echo json_encode((string)$this->lang('Confirm')) ?>,
		close: <?php echo json_encode((string)$this->lang('Close')) ?>,
		error: <?php echo json_encode((string)$this->lang('The site status could not be changed.')) ?>
	};

	function render(closed){
		changing = true;
		input.prop('checked', closed);
		status.toggleClass('is-closed', closed).toggleClass('is-open', !closed);
		status.find('.maintenance-status-label').text(closed ? labels.closed : labels.open);
		status.find('.maintenance-status-description').text(closed ? labels.closedDescription : labels.openDescription);
		changing = false;
	}

	function confirmChange(closed){
		var title = closed ? labels.closeTitle : labels.openTitle;
		var question = closed ? labels.closeQuestion : labels.openQuestion;
		var confirmClass = closed ? 'negative' : 'teal';
		var confirmIcon = closed ? 'fas fa-power-off' : 'fas fa-door-open';
		var modal = $('<div class="ui small modal maintenance-confirm" role="dialog">'
			+ '<div class="header"><i class="'+confirmIcon+'"></i> '+title+'<i class="close icon" aria-label="'+labels.close+'"></i></div>'
			+ '<div class="content"><p>'+question+'</p></div>'
			+ '<div class="actions">'
			+ '<button type="button" class="ui secondary button cancel">'+labels.cancel+'</button>'
			+ '<button type="button" class="ui '+confirmClass+' button maintenance-confirm-submit">'+labels.confirm+'</button>'
			+ '</div></div>').appendTo('body');

		modal.modal({
			autofocus: false,
			closable: true,
			onHidden: function(){ modal.remove(); }
		}).modal('show');

		modal.find('.maintenance-confirm-submit').on('click', function(){
			var button = $(this);
			button.addClass('loading disabled');
			toggle.addClass('loading disabled');

			$.ajax({
				url: '<?php echo url('admin/ajax/settings/maintenance.json') ?>',
				type: 'POST',
				dataType: 'json',
				data: {closed: closed ? 1 : 0}
			}).done(function(data){
				var saved = !!data.status;
				render(saved);
				notify(saved ? labels.closed : labels.open, 'success');
				modal.modal('hide');
			}).fail(function(){
				notify(labels.error, 'danger');
			}).always(function(){
				button.removeClass('loading disabled');
				toggle.removeClass('loading disabled');
			});
		});
	}

	if (typeof $.fn.checkbox == 'function') toggle.checkbox();

	input.on('change', function(){
		if (changing) return;

		var requested = input.is(':checked');
		render(!requested);
		confirmChange(requested);
	});
});
