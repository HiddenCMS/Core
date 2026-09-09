(function(){
	var siteKey = <?php echo json_encode((string)$this->config->captcha_public_key) ?>;
	var loading;

	function loadApi(){
		if (window.grecaptcha){
			return $.Deferred().resolve().promise();
		}

		if (!loading){
			loading = $.getScript('https://www.google.com/recaptcha/api.js?render=' + encodeURIComponent(siteKey) + '&hl=<?php echo $this->config->lang->info()->name ?>&_=' );
		}

		return loading;
	}

	function displayError(form){
		var $form = $(form);
		$form.find('.recaptcha-v3-error').remove();
		$('<div class="ui negative message recaptcha-v3-error">La vérification anti-robots est indisponible. Veuillez réessayer.</div>')
			.prependTo($form.children('.content:first').length ? $form.children('.content:first') : $form);
	}

	function setLoading(form, active){
		$(form).find('button[type="submit"], input[type="submit"]')
			.prop('disabled', active)
			.toggleClass('loading', active);
	}

	document.addEventListener('submit', function(event){
		var form = event.target;
		var token = form.querySelector('.recaptcha-v3-token');

		if (!token || form.dataset.recaptchaReady === '1'){
			if (form.dataset.recaptchaReady === '1'){
				delete form.dataset.recaptchaReady;
			}
			return;
		}

		event.preventDefault();
		event.stopImmediatePropagation();

		if (form.dataset.recaptchaPending === '1'){
			return;
		}

		form.dataset.recaptchaPending = '1';
		setLoading(form, true);

		loadApi().then(function(){
			grecaptcha.ready(function(){
				grecaptcha.execute(siteKey, {action: token.dataset.recaptchaAction || 'submit'}).then(function(value){
					token.value = value;
					delete form.dataset.recaptchaPending;
					form.dataset.recaptchaReady = '1';
					setLoading(form, false);
					if (form.requestSubmit){
						event.submitter ? form.requestSubmit(event.submitter) : form.requestSubmit();
					} else {
						$(form).trigger('submit');
					}
				}).catch(function(){
					delete form.dataset.recaptchaPending;
					setLoading(form, false);
					displayError(form);
				});
			});
		}).fail(function(){
			delete form.dataset.recaptchaPending;
			setLoading(form, false);
			displayError(form);
		});
	}, true);

	if (siteKey){
		loadApi();
	}
})();
