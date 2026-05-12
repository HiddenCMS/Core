var modal = new function(){
	var _modals = {};
	var _scripts;
	var _fallbackClass = 'is-open';
	var _transitionMs = 180;
	var _modalClass = 'hb-modal';
	var _buttonToneRegex = /\bhb-btn-(primary|secondary|success|info|warning|danger|dark|light)\b/;

	var buildBasicModal = function(bodyHtml){
		return '<div class="modal fade ' + _modalClass + '" tabindex="-1" role="dialog" aria-hidden="true">'
			+ '<div class="modal-dialog" role="document">'
			+ '<div class="modal-content">'
			+ '<div class="modal-header">'
			+ '<h5 class="modal-title">Edition</h5>'
			+ '<button type="button" class="close" data-dismiss="modal" aria-label="Fermer"><span aria-hidden="true">&times;</span></button>'
			+ '</div>'
			+ '<div class="modal-body">' + (bodyHtml || '') + '</div>'
			+ '</div>'
			+ '</div>'
			+ '</div>';
	};

	var isFomantic = function($modal){
		return $modal.hasClass('ui') && $modal.hasClass('modal') && typeof $.fn.modal === 'function';
	};

	var normalizeModalMarkup = function($modal){
		$modal.find('.modal-header').each(function(){
			$(this).removeClass('modal-header').addClass('header');
		});

		$modal.find('.modal-body').each(function(){
			$(this).removeClass('modal-body').addClass('content');
		});

		$modal.find('.modal-footer').each(function(){
			$(this).removeClass('modal-footer').addClass('actions');
		});

		$modal.find('button.close[data-dismiss="modal"]').each(function(){
			$(this).replaceWith('<i class="close icon"></i>');
		});

		$modal.find('[data-dismiss="modal"]').addClass('cancel');
		$modal.find('.hb-btn').addClass('ui button');
	};

	var normalizeModalButtons = function($modal){
		$modal.find('.hb-btn').each(function(){
			var $button = $(this);

			if ($button.hasClass('close'))
			{
				return;
			}

			if (!$button.hasClass('hb-btn-outline') && !_buttonToneRegex.test($button.attr('class') || ''))
			{
				$button.addClass('hb-btn-secondary');
			}
		});
	};

	var fallbackOpen = function($modal){
		var timer = $modal.data('hbModalHideTimer');

		if (timer){
			clearTimeout(timer);
			$modal.removeData('hbModalHideTimer');
		}

		$modal
			.addClass(_modalClass)
			.addClass(_fallbackClass)
			.attr('aria-hidden', 'false')
			.css('display', 'flex');

		window.requestAnimationFrame(function(){
			$modal.addClass('show');
		});

		$('body').addClass('hb-modal-open');
	};

	var fallbackHide = function($modal, onHidden){
		var complete = function(){
			$modal
				.removeClass(_fallbackClass)
				.removeClass('show')
				.attr('aria-hidden', 'true')
				.hide();

			if (!$('.' + _modalClass + '.' + _fallbackClass + ', .modal.' + _fallbackClass + ', .ui.modal.active:visible, .modal.show:visible').length){
				$('body').removeClass('hb-modal-open');
			}

			if (typeof onHidden === 'function'){
				onHidden();
			}
		};

		$modal.removeClass('show').attr('aria-hidden', 'true');

		var timer = setTimeout(complete, _transitionMs);
		$modal.data('hbModalHideTimer', timer);
	};

	var openModal = function($modal){
		$modal.addClass(_modalClass);
		normalizeModalButtons($modal);

		if (isFomantic($modal))
		{
			normalizeModalMarkup($modal);

			$modal
				.modal({
					allowMultiple: true,
					detachable: false,
					autofocus: false,
					observeChanges: true
				})
				.modal('show');

			return;
		}

		fallbackOpen($modal);
	};

	var hideModal = function($modal, onHidden){
		if (isFomantic($modal))
		{
			$modal
				.modal({
					onHidden: function(){
						if (typeof onHidden === 'function')
						{
							onHidden();
						}
					}
				})
				.modal('hide');

			return;
		}

		fallbackHide($modal, onHidden);
	};

	var hideVisibleModals = function(){
		$('.' + _modalClass + '.' + _fallbackClass + ', .modal.' + _fallbackClass).each(function(){
			hideModal($(this));
		});

		$('.ui.modal.active:visible').each(function(){
			hideModal($(this));
		});

		$('.modal.show').each(function(){
			hideModal($(this));
		});
	};

	this.exec = function(callback){
		return function(data){
			if (typeof data.success != 'undefined' && data.success == 'refresh'){
				location.reload();
				return;
			}

			if (typeof data.redirect != 'undefined'){
				window.location.href = data.redirect;
				return;
			}

			if (typeof data.css != 'undefined'){
				$('head').append(data.css);
			}

			var promises = [];

			if (typeof data.js != 'undefined'){
				if (typeof _scripts == 'undefined'){
					_scripts = [];

					$('script').each(function(){
						var src = $(this).attr('src');

						if (typeof src != 'undefined'){
							_scripts.push(src);
						}
					});
				}

				$.each(data.js, function(_, js){
					if ($.inArray(js, _scripts) == -1){
						var d = $.Deferred();

						$.when.apply($, promises).then(function(){
							$.getScript(js).then(function(){
								_scripts.push(js);
								d.resolve();
							});
						});

						promises.push(d);
					}
				});
			}

			if (typeof data.notify != 'undefined'){
				$.each(data.notify, function(_, n){
					notify(n.message, n.type);
				});
			}

			$.when.apply($, promises).then(function(){
				callback(data);
			});
		};
	};

	this.load = function(url){
		var show = function(){
			hideVisibleModals();
			openModal(_modals[url]);
		};

		if (typeof _modals[url] == 'undefined'){
			$.ajax({
				url: url,
				cache: false,
				success: this.exec(function(data){
					if (typeof data === 'string' && data.length){
						try {
							var parsed = $.parseJSON(data);

							if (parsed && typeof parsed === 'object'){
								data = parsed;
							}
						}
						catch (e){}
					}

					var content = '';

					if (typeof data.content != 'undefined' && data.content){
						content = data.content;
					}
					else if (typeof data.form != 'undefined' && data.form){
						content = buildBasicModal(data.form);
					}
					else if (typeof data === 'string' && data.length){
						content = buildBasicModal(data);
					}

					if (content){
						var $modalNode = $(content).appendTo('body');
						var $modal = _modals[url] = $modalNode.hasClass('modal') ? $modalNode : $modalNode.closest('.modal');

						if (!$modal.length){
							$modalNode.remove();
							return;
						}

						$modal.addClass(_modalClass);
						normalizeModalButtons($modal);

						$('body').trigger('nf.load');

						var $form = $modal.find('form');

						if (typeof form != 'undefined' && $form.length){
							$modal.on('submit', 'form', function(e){
								e.preventDefault();

								var $submit = $modal.find('[type="submit"]');

								if ($submit.hasClass('disabled')){
									return;
								}

								$submit.addClass('disabled');

								form.submit($form).then(function(data){
									$submit.removeClass('disabled');

									if (typeof data.modal != 'undefined' && data.modal == 'dispose'){
										hideModal($modal, function(){
											$modal.remove();
											delete _modals[url];
										});
									}
								});
							});

							form.load($form);
						}

						show();
					}
					else if (window.console && typeof window.console.warn === 'function'){
						console.warn('[modal] Empty response payload for', url, data);
					}
				})
			});
		}
		else {
			show();
		}
	};

	this.hide = function($modal, onHidden){
		hideModal($modal, onHidden);
	};

	this.open = function($modal){
		hideVisibleModals();
		openModal($modal);
	};

	return this;
};

$(function(){
	$(document).on('click', '[data-modal-ajax]', function(e){
		modal.load($(this).data('modal-ajax'));
		e.preventDefault();
	});

	$(document).on('click', '[data-toggle="modal"][data-target]', function(e){
		var target = $(this).data('target');
		var $modal = $(target);

		if ($modal.length){
			modal.open($modal);
			e.preventDefault();
		}
	});

	$(document).on('click', '.modal [data-dismiss="modal"], .ui.modal [data-dismiss="modal"]', function(e){
		var $modal = $(this).closest('.modal');
		modal.hide($modal);
		e.preventDefault();
	});

	$(document).on('click', '.hb-modal', function(e){
		if (e.target === this){
			modal.hide($(this));
		}
	});
});
