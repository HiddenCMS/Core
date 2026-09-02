var modal = new function(){
	var _modals = {};
	var _scripts;
	var _fallbackClass = 'is-open';
	var _fallbackBodyClass = 'modal-fallback-open';
	var _transitionMs = 180;
	var _modalClass = 'modal-managed';

	var formSubmitButtons = function($modal, $form){
		return $modal.find('[type="submit"]').filter(function(){
			return this.form === $form[0];
		});
	};

	var setFormProcessing = function($modal, $form, $submit, processing){
		var $status = $modal.children('.modal-processing-status:first');

		$modal.toggleClass('is-processing', processing).attr('aria-busy', processing ? 'true' : 'false');
		$submit.toggleClass('loading disabled', processing).attr('aria-disabled', processing ? 'true' : 'false');

		if (!processing){
			$status.remove();
			return;
		}

		var action = $form.attr('action') || '';
		var isInstall = action.indexOf('/addons/install') !== -1;
		var isCoreUpdate = action.indexOf('/settings/core-update') !== -1;
		var isAddonUpdate = action.indexOf('/settings/addon-update') !== -1;
		var isBackup = action.indexOf('/settings/backup') !== -1;
		var isRollback = action.indexOf('/settings/rollback') !== -1;
		var title = isInstall ? 'Installation en cours'
			: isCoreUpdate ? 'Mise à jour en cours'
			: isAddonUpdate ? 'Mise à jour de l\'addon'
			: isBackup ? 'Sauvegarde en cours'
			: isRollback ? 'Restauration en cours'
			: 'Traitement en cours';
		var detail = isInstall
			? 'Composer télécharge et configure le paquet. Cette opération peut prendre quelques instants.'
			: isCoreUpdate
				? 'HiddenCMS sauvegarde le site, installe le core et applique les migrations.'
				: isAddonUpdate
					? 'Composer installe la dernière version compatible puis HiddenCMS applique les migrations de l\'addon.'
				: isBackup
					? 'La base de données et les fichiers gérés sont en cours d\'archivage.'
					: isRollback
						? 'Les fichiers et la base de données sont restaurés depuis le point de retour.'
						: 'Merci de patienter pendant la finalisation de cette opération.';

		if (!$status.length){
			$status = $('<div class="modal-processing-status" role="status" aria-live="polite">'
				+ '<div class="ui active inline loader"></div>'
				+ '<div><strong></strong><span></span></div>'
				+ '</div>');
			$status.insertBefore($modal.children('.actions:first'));
		}

		$status.find('strong').text(title);
		$status.find('span').text(detail);
	};

	var buildBasicModal = function(bodyHtml){
		return '<div class="ui modal" tabindex="-1" role="dialog" aria-hidden="true">'
			+ '<div class="header">'
			+ 'Edition'
			+ '<i class="close icon" data-dismiss="modal" aria-label="Fermer"></i>'
			+ '</div>'
			+ '<div class="content">' + (bodyHtml || '') + '</div>'
			+ '</div>';
	};

	var hasModalRoot = function(content){
		return /^\s*<(?:div|form)\b[^>]*\bclass=["'][^"']*\bmodal\b/i.test(content);
	};

	var isFomantic = function($modal){
		return $modal.hasClass('ui') && $modal.hasClass('modal') && typeof $.fn.modal === 'function' && typeof $.fn.modal.settings !== 'undefined';
	};

	var normalizeModalMarkup = function($modal){
		$modal.children('.modal-dialog').each(function(){
			var $dialog = $(this);
			var $content = $dialog.children('.modal-content:first');

			if ($content.length){
				$content.children().appendTo($modal);
			}
			else {
				$dialog.children().appendTo($modal);
			}

			$dialog.remove();
		});

		$modal.children('.modal-content').each(function(){
			$(this).children().appendTo($modal);
			$(this).remove();
		});

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
	};

	var fallbackOpen = function($modal){
		var timer = $modal.data('modalHideTimer');

		if (timer){
			clearTimeout(timer);
			$modal.removeData('modalHideTimer');
		}

		$modal
			.addClass(_modalClass)
			.addClass(_fallbackClass)
			.addClass('active')
			.attr('aria-hidden', 'false')
			.css('display', 'flex');

		window.requestAnimationFrame(function(){
			$modal.addClass('show');
			$modal.trigger('shown.bs.modal');
		});

		$('body').addClass('modal-open');
		$('body').addClass(_fallbackBodyClass);
	};

	var fallbackHide = function($modal, onHidden){
		var complete = function(){
			$modal
				.removeClass(_fallbackClass)
				.removeClass('show')
				.removeClass('active')
				.attr('aria-hidden', 'true')
				.hide();

			if (!$('.' + _modalClass + '.' + _fallbackClass + ', .modal.' + _fallbackClass + ', .ui.modal.active:visible, .modal.show:visible').length){
				$('body').removeClass('modal-open');
				$('body').removeClass(_fallbackBodyClass);
			}

			if (typeof onHidden === 'function'){
				onHidden();
			}

			$modal.trigger('hidden.bs.modal');
		};

		$modal.removeClass('show active').attr('aria-hidden', 'true');

		var timer = setTimeout(complete, _transitionMs);
		$modal.data('modalHideTimer', timer);
	};

	var openModal = function($modal){
		var event = $.Event('show.bs.modal');

		$modal.trigger(event);

		if (event.isDefaultPrevented())
		{
			return;
		}

		if ($modal.hasClass('ui') && $modal.hasClass('modal'))
		{
			normalizeModalMarkup($modal);

			if (isFomantic($modal))
			{
				$modal
					.modal({
						allowMultiple: true,
						autofocus: false,
						observeChanges: true,
						onVisible: function(){
							$modal.trigger('shown.bs.modal');
						}
					})
					.modal('show');

				return;
			}
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

						$modal.trigger('hidden.bs.modal');
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

			if (typeof data.error != 'undefined'){
				notify(data.error, 'danger');
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
						var formContent = $.isArray(data.form) ? data.form.join('') : String(data.form);
						content = hasModalRoot(formContent) ? formContent : buildBasicModal(formContent);
					}
					else if (typeof data === 'string' && data.length){
						content = hasModalRoot(data) ? data : buildBasicModal(data);
					}

					if (content){
						var $modalNode = $(content).appendTo('body');
						var $modal = _modals[url] = $modalNode.hasClass('modal') ? $modalNode : $modalNode.closest('.modal');

						if (!$modal.length){
							$modalNode.remove();
							return;
						}

						$('body').trigger('nf.load');

						var $form = $modal.is('form') ? $modal : $modal.find('form');

						if (typeof form != 'undefined' && $form.length){
							$form.on('submit', function(e){
								e.preventDefault();

								var $submit = formSubmitButtons($modal, $form);

								if ($submit.hasClass('disabled')){
									return;
								}

								setFormProcessing($modal, $form, $submit, true);

								form.submit($form).then(function(data){
									if (typeof data.modal != 'undefined' && data.modal == 'dispose'){
										hideModal($modal, function(){
											$modal.remove();
											delete _modals[url];
										});
									}
								}).always(function(){
									setFormProcessing($modal, $form, $submit, false);
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

	$(document).on('click', '[data-modal-target]', function(e){
		var target = $(this).data('modal-target');
		var $modal = $(target);

		if ($modal.length){
			modal.open($modal);
			e.preventDefault();
		}
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

	$(document).on('click', '.modal-managed', function(e){
		if (e.target === this){
			modal.hide($(this));
		}
	});
});
