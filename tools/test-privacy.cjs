'use strict';
// Install jsdom as a test-only dependency, or expose it through NODE_PATH.
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const { execFileSync } = require('node:child_process');
const { join } = require('node:path');
const { JSDOM, ResourceLoader, VirtualConsole } = require('jsdom');
const root = join(__dirname, '..');
const html = execFileSync('php', [join(__dirname, 'test-privacy.php'), '--fixture'], { cwd: root, encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe'] });
const script = readFileSync(join(root, 'dist/js/privacy.js'), 'utf8');
const key = 'hiddencms.privacy.v1';
let checks = 0;
function check(value, message) { assert.ok(value, message); checks++; console.log('PASS ' + message); }
async function fixture(saved, storageFails = false, noServicesUsed = false) {
	const requests = [];
	const errors = [];
	let reloads = 0;
	class Resources extends ResourceLoader {
		fetch(url) { requests.push(url); const request = Promise.resolve(Buffer.from('<html><body>Test media</body></html>')); request.abort = () => {}; return request; }
	}
	const console = new VirtualConsole();
	console.on('jsdomError', error => { if (/navigation/.test(error.message)) reloads++; else errors.push(error); });
	const dom = new JSDOM(html, { url: 'https://cms.example.test/fr/', runScripts: 'outside-only', resources: new Resources(), virtualConsole: console });
	const w = dom.window;
	const d = w.document;
	const config = JSON.parse(d.getElementById('privacy-config').textContent);
	if (noServicesUsed) {
		delete config.services.analytics;
		d.getElementById('privacy-config').textContent = JSON.stringify(config);
		d.querySelector('.privacy-embed').remove();
	}
	if (saved !== undefined) w.localStorage.setItem(key, typeof saved === 'string' ? saved : JSON.stringify(saved(config)));
	if (storageFails) {
		w.Storage.prototype.setItem = () => { throw new Error('Storage denied'); };
		w.Storage.prototype.getItem = () => { throw new Error('Storage denied'); };
	}
	w.HTMLDialogElement.prototype.showModal = function () { this.open = true; };
	w.HTMLDialogElement.prototype.close = function () { this.open = false; };
	w.eval(script);
	await new Promise(resolve => setImmediate(resolve));
	return { w, d, config, requests, errors, reloads: () => reloads, close: () => dom.window.close(), click: action => d.querySelector('#privacy-dialog [data-privacy-choice="' + action + '"]').click() };
}
function saved(config, choices = { youtube: true, analytics: true }) {
	return { version: config.version, createdAt: Date.now() - 1000, expiresAt: Date.now() + 60000, choices };
}
(async () => {
	let f = await fixture();
	try {
		check(!f.d.querySelector('iframe') && !f.d.querySelector('script[src*="googletagmanager"]') && f.requests.length === 0, 'No third-party resource before consent');
		check(!f.d.getElementById('privacy-banner').hidden, 'First visit prompts when a service is used');
		check(f.d.getElementById('privacy-fallback').hidden, 'Footer trigger prevents a duplicate floating trigger');
		f.w.HiddenCMSPrivacy.open();
		check(f.d.getElementById('privacy-dialog').open && !f.d.querySelector('[data-privacy-toggle]:checked'), 'Preferences open with no preselection');
		f.d.querySelector('[data-privacy-close]').click();
		check(!f.d.getElementById('privacy-dialog').open && f.w.localStorage.getItem(key) === null, 'Closing is not consent');
		f.click('reject');
		check(f.requests.length === 0 && !f.w.HiddenCMSPrivacy.hasConsent('analytics'), 'Rejecting sends no third-party request');
		let value = JSON.parse(f.w.localStorage.getItem(key));
		check(value.choices.youtube === false && value.choices.analytics === false && value.expiresAt - value.createdAt === 180 * 86400000, 'Refusal is saved for the same duration as acceptance');
		f.w.HiddenCMSPrivacy.open();
		f.d.querySelector('[data-privacy-toggle="youtube"]').checked = true;
		f.click('save');
		check(f.d.querySelector('iframe') && f.requests.length === 1 && f.requests[0].includes('youtube-nocookie.com'), 'Granting only YouTube loads only the video');
		check(!f.d.querySelector('script[src*="googletagmanager"]') && !f.w.HiddenCMSPrivacy.hasConsent('analytics'), 'Granular media consent does not grant analytics');
		f.w.HiddenCMSPrivacy.open();
		check(f.d.querySelector('[data-privacy-toggle="youtube"]').checked, 'Reopening reflects saved choices');
		f.click('reject');
		check(!f.d.querySelector('iframe') && !f.d.querySelector('.privacy-embed-placeholder').hidden, 'Withdrawing media consent removes the iframe');
		f.w.document.cookie = '_ga=test; path=/';
		f.w.document.cookie = 'session=keep; path=/';
		f.click('accept');
		check(f.d.querySelectorAll('script[src*="googletagmanager"]').length === 1 && f.w.HiddenCMSPrivacy.hasConsent('analytics'), 'Analytics loads only after explicit consent');
		check(f.w.dataLayer[0][2].ad_storage === 'denied', 'Advertising remains denied after audience consent');
		f.click('accept');
		check(f.d.querySelectorAll('script[src*="googletagmanager"]').length === 1, 'Repeated acceptance does not duplicate analytics');
		f.click('reject');
		check(f.w['ga-disable-G-TEST12345'] === true && f.reloads() === 1, 'Withdrawing analytics disables measurement and reloads the page');
		check(!f.w.document.cookie.includes('_ga=') && f.w.document.cookie.includes('session=keep'), 'Withdrawal clears analytics cookies but preserves authentication');
		check(f.errors.length === 0, 'No DOM runtime errors');
	} finally { f.close(); }
	for (const [value, label] of [
		['invalid-json', 'Malformed JSON'],
		[c => ({ ...saved(c), version: 'old' }), 'Old policy version'],
		[c => ({ ...saved(c), expiresAt: Date.now() - 1 }), 'Expired consent'],
		[c => ({ ...saved(c), createdAt: Date.now() + 1000 }), 'Future timestamp'],
		[c => ({ ...saved(c), choices: { youtube: 'true', analytics: 1 } }), 'Non-boolean choices'],
		[c => ({ ...saved(c), expiresAt: Date.now() + 365 * 86400000 }), 'Excessive lifetime']
	]) {
		f = await fixture(value);
		try { check(!f.d.querySelector('iframe') && !f.d.querySelector('script[src*="googletagmanager"]') && f.requests.length === 0, label + ' fails closed'); }
		finally { f.close(); }
	}
	f = await fixture(c => saved(c, { youtube: false, analytics: false }));
	try { check(f.d.getElementById('privacy-banner').hidden && f.requests.length === 0, 'Saved refusal suppresses repeat prompts without third-party requests'); }
	finally { f.close(); }
	f = await fixture(c => saved(c, { youtube: true, analytics: false }));
	try {
		check(!!f.d.querySelector('iframe') && f.requests.length === 1, 'Saved media acceptance is restored');
		f.w.localStorage.setItem(key, JSON.stringify(saved(f.config, { youtube: false, analytics: false })));
		f.w.dispatchEvent(new f.w.StorageEvent('storage', { key }));
		check(!f.d.querySelector('iframe'), 'Refusal in another tab unloads media');
		f.w.localStorage.setItem(key, JSON.stringify(saved(f.config)));
		f.w.dispatchEvent(new f.w.StorageEvent('storage', { key }));
		f.w.localStorage.removeItem(key);
		f.w.dispatchEvent(new f.w.StorageEvent('storage', { key }));
		check(!f.d.querySelector('iframe') && f.w['ga-disable-G-TEST12345'] && f.reloads() === 1, 'Clearing storage in another tab revokes active services');
	} finally { f.close(); }
	f = await fixture(undefined, true);
	try { f.click('reject'); check(f.requests.length === 0 && !f.d.querySelector('.privacy-storage-warning').hidden, 'Unavailable storage fails closed and reports persistence failure'); }
	finally { f.close(); }
	f = await fixture(c => saved(c));
	try {
		f.w.Date.now = () => Date.now() + 120000;
		f.d.dispatchEvent(new f.w.Event('visibilitychange'));
		// jsdom starts hidden; pageshow also covers a restored cached page.
		f.w.dispatchEvent(new f.w.Event('pageshow'));
		check(!f.d.querySelector('iframe') && f.w['ga-disable-G-TEST12345'] && f.reloads() === 1, 'Consent expiry unloads previously active services');
	} finally { f.close(); }
	f = await fixture(c => saved(c, { youtube: true, analytics: false }));
	try {
		const other = f.d.querySelector('.privacy-embed').cloneNode(true);
		other.querySelector('iframe').remove();
		f.d.body.appendChild(other);
		await new Promise(resolve => setImmediate(resolve));
		check(f.d.querySelectorAll('iframe').length === 2, 'AJAX media honors existing consent');
		f.click('reject');
		check(f.d.querySelectorAll('iframe').length === 0, 'Withdrawal also removes dynamically inserted media');
		const unsafe = other.cloneNode(true);
		unsafe.dataset.privacySrc = 'https://www.youtube.com.evil.test/embed/id';
		f.d.body.appendChild(unsafe);
		f.click('accept');
		check(!unsafe.querySelector('iframe'), 'Client also rejects an altered untrusted embed host');
	} finally { f.close(); }
	f = await fixture(undefined, false, true);
	try { check(f.d.getElementById('privacy-banner').hidden && f.requests.length === 0, 'No unsolicited banner when no optional service is used'); }
	finally { f.close(); }
	console.log(checks + ' checks passed. All remote resource loads were intercepted, not sent.');
})().catch(error => { console.error(error); process.exitCode = 1; });
