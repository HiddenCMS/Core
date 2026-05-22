function notify(message, type) {
	if (typeof type == 'undefined') {
		type = 'success';
	}

	$(function(){
		$.notify({
			message: message
		},{
			mouse_over: 'pause',
			newest_on_top: true,
			type: type,
			placement: {
				from: 'top',
				align: 'right'
			},
			offset: {
				x: 16,
				y: 16
			},
			template: '<div data-notify="container" class="notification is-{0}" role="alert">' +
						'<button type="button" class="delete" data-notify="dismiss" aria-label="Close"></button>' +
						'<span data-notify="message">{2}</span>' +
					'</div>'
		});
	});
}
