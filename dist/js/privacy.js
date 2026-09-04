(function () {
	'use strict';
	var configElement = document.getElementById('privacy-config');
	if (!configElement || window.HiddenCMSPrivacy) return;
	var config = JSON.parse(configElement.textContent);
	var services = config.services;
	var key = 'hiddencms.privacy.v1';
	var duration = 180 * 24 * 60 * 60 * 1000;
	var banner = document.getElementById('privacy-banner');
	var dialog = document.getElementById('privacy-dialog');
	var handlers = {};
	var active = {};
	var expiryTimer;
	var lastFocus;
	var state = read();

	function valid(value) {
		return value && value.version === config.version && Number.isFinite(value.createdAt) && Number.isFinite(value.expiresAt)
			&& value.createdAt <= Date.now() && value.expiresAt > Date.now() && value.expiresAt > value.createdAt
			&& value.expiresAt - value.createdAt <= duration && value.choices && typeof value.choices === 'object'
			&& Object.keys(services).every(function (id) { return typeof value.choices[id] === 'boolean'; });
	}
	function read() {
		try {
			var value = JSON.parse(localStorage.getItem(key));
			return valid(value) ? value : null;
		} catch (_) { return null; }
	}
	function allowed(id) { return !!(valid(state) && state.choices[id] === true && services[id]); }
	function required() {
		return !!services.analytics || !!document.querySelector('[data-privacy-service]');
	}
	function inputs() {
		dialog.querySelectorAll('[data-privacy-toggle]').forEach(function (input) { input.checked = allowed(input.dataset.privacyToggle); });
	}
	function open() {
		inputs();
		lastFocus = document.activeElement;
		if (!dialog.open) dialog.showModal();
	}
	function close() {
		dialog.close();
		if (lastFocus && lastFocus.isConnected) lastFocus.focus();
	}
	function expire() {
		clearTimeout(expiryTimer);
		if (!state) return;
		expiryTimer = setTimeout(function () {
			if (!valid(state)) { state = null; apply(); }
			else expire();
		}, Math.min(Math.max(1, state.expiresAt - Date.now()), 2147483647));
	}
	function embeds() {
		document.querySelectorAll('[data-privacy-service][data-privacy-src]').forEach(function (element) {
			var id = element.dataset.privacyService;
			var placeholder = element.querySelector('.privacy-embed-placeholder');
			var frame = element.querySelector('iframe');
			if (!allowed(id)) {
				if (frame) frame.remove();
				if (placeholder) placeholder.hidden = false;
				return;
			}
			if (frame) return;
			var source;
			try { source = new URL(element.dataset.privacySrc); } catch (_) { return; }
			if (source.protocol !== 'https:' || source.username || source.password || source.port || services[id].hosts.indexOf(source.hostname) < 0) return;
			frame = document.createElement('iframe');
			frame.title = element.dataset.privacyTitle || services[id].title;
			frame.referrerPolicy = 'no-referrer';
			frame.allowFullscreen = true;
			frame.src = source.href;
			if (placeholder) placeholder.hidden = true;
			element.appendChild(frame);
		});
	}
	function apply() {
		var reload = false;
		Object.keys(handlers).forEach(function (id) {
			var next = allowed(id);
			if (next === !!active[id]) return;
			if (next) {
				try { handlers[id].enable(); active[id] = true; }
				catch (error) { console.error('Privacy service failed to start:', id, error); }
			} else {
				active[id] = false;
				try { handlers[id].disable(); }
				catch (error) { console.error('Privacy service failed to stop:', id, error); reload = true; }
				reload = reload || handlers[id].reloadOnRevoke === true;
			}
		});
		embeds();
		banner.hidden = !!valid(state) || !required();
		expire();
		window.dispatchEvent(new CustomEvent('hiddencms:privacychange', { detail: { choices: Object.keys(services).reduce(function (all, id) { all[id] = allowed(id); return all; }, {}) } }));
		// Removing a script element cannot stop code it has already executed.
		if (reload) window.location.reload();
	}
	function choose(action) {
		var choices = {};
		Object.keys(services).forEach(function (id) { choices[id] = action === 'accept'; });
		if (action === 'save') dialog.querySelectorAll('[data-privacy-toggle]').forEach(function (input) { choices[input.dataset.privacyToggle] = input.checked; });
		state = { version: config.version, createdAt: Date.now(), expiresAt: Date.now() + duration, choices: choices };
		var storageWarning = dialog.querySelector('.privacy-storage-warning');
		storageWarning.hidden = true;
		try { localStorage.setItem(key, JSON.stringify(state)); }
		catch (_) {
			try { localStorage.removeItem(key); } catch (_) { /* Storage may be completely unavailable. */ }
			storageWarning.hidden = false;
		}
		if (storageWarning.hidden) close();
		else if (!dialog.open) open();
		apply();
	}
	function register(id, handler) {
		if (!services[id] || handlers[id] || typeof handler.enable !== 'function' || typeof handler.disable !== 'function') return false;
		handlers[id] = handler;
		apply();
		return true;
	}
	function clearAnalyticsCookies() {
		var names = document.cookie.split(';').map(function (cookie) { return cookie.trim().split('=')[0]; })
			.filter(function (name) { return /^(?:_ga(?:_|$)|_gid$|_gat(?:_|$))/.test(name); });
		var domains = [''];
		var parts = window.location.hostname.split('.');
		while (parts.length > 1) { domains.push(parts.join('.')); parts.shift(); }
		var paths = ['/'];
		var segments = window.location.pathname.split('/');
		while (segments.length > 1) { paths.push(segments.join('/')); segments.pop(); }
		names.forEach(function (name) {
			domains.forEach(function (domain) {
				paths.forEach(function (path) {
					document.cookie = name + '=; Max-Age=0; path=' + path + (domain ? '; domain=' + domain : '') + '; SameSite=Lax';
				});
			});
		});
	}
	window.HiddenCMSPrivacy = { hasConsent: allowed, open: open, register: register };
	if (services.analytics) {
		var measurementId = services.analytics.measurementId;
		window['ga-disable-' + measurementId] = !allowed('analytics');
		if (!allowed('analytics')) clearAnalyticsCookies();
		register('analytics', {
			enable: function () {
				window['ga-disable-' + measurementId] = false;
				window.dataLayer = window.dataLayer || [];
				window.gtag = function () { window.dataLayer.push(arguments); };
				window.gtag('consent', 'default', { analytics_storage: 'granted', ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied' });
				window.gtag('js', new Date());
				window.gtag('config', measurementId, { allow_google_signals: false, allow_ad_personalization_signals: false, cookie_path: '/', cookie_flags: 'SameSite=Lax' });
				var script = document.createElement('script');
				script.async = true;
				script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(measurementId);
				document.head.appendChild(script);
			},
			disable: function () { window['ga-disable-' + measurementId] = true; clearAnalyticsCookies(); },
			reloadOnRevoke: true
		});
	}
	document.addEventListener('click', function (event) {
		if (event.target.closest('[data-privacy-open]')) { event.preventDefault(); open(); }
		if (event.target.closest('[data-privacy-close]')) close();
		var choice = event.target.closest('[data-privacy-choice]');
		if (choice) choose(choice.dataset.privacyChoice);
	});
	dialog.addEventListener('click', function (event) { if (event.target === dialog) close(); });
	dialog.addEventListener('cancel', function (event) { event.preventDefault(); close(); });
	window.addEventListener('storage', function (event) { if (event.key === key || event.key === null) { state = read(); inputs(); apply(); } });
	window.addEventListener('pageshow', function () { state = read(); apply(); });
	document.addEventListener('visibilitychange', function () { if (!document.hidden) { state = read(); apply(); } });
	new MutationObserver(function () { embeds(); banner.hidden = !!valid(state) || !required(); }).observe(document.body, { childList: true, subtree: true });
	var fallback = document.getElementById('privacy-fallback');
	fallback.hidden = !!document.querySelector('footer [data-privacy-open]');
	apply();
}());
