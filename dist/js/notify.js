function notify(message, type) {
	if (typeof type == 'undefined') {
		type = 'success';
	}

	var map = {
		success: 'success',
		error: 'error',
		danger: 'error',
		warning: 'warning',
		info: 'info'
	};

	var tone = map[type] || map.success;

	if ($.fn.toast) {
		$('body').toast({
			class: tone,
			message: message,
			position: 'top right',
			showProgress: 'bottom',
			displayTime: 5000
		});

		return;
	}

	var fallbackTone = tone == 'error' ? 'negative' : (tone == 'success' ? 'positive' : tone);
	var $notification = $('<div class="ui ' + fallbackTone + ' message admin-notification"></div>');

	$notification
		.append($('<i class="close icon" aria-label="Fermer"></i>'))
		.append($('<div class="content"></div>').text(message))
		.appendTo($('<div class="admin-notifications" aria-live="polite" aria-atomic="true"></div>').appendTo('body'));

	var close = function(){
		$notification.addClass('is-hiding');

		setTimeout(function(){
			$notification.remove();
		}, 180);
	};

	$notification.find('.close').on('click', close);
	setTimeout(close, 5000);
}
