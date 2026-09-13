const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const source = fs.readFileSync(require('node:path').join(__dirname, '../dist/js/site-statistics.js'), 'utf8');
function fixture(allowed, pathname = '/fr/page') {
    const calls = [], events = {};
    const privacy = {hasConsent: () => allowed};
    vm.runInNewContext(source, {
        window: {HiddenCMSPrivacy: privacy, addEventListener: (name, fn) => {events[name] = fn;}},
        document: {getElementById: () => ({textContent: JSON.stringify({url:'/ajax/statistics/view.json',token:'test'})}),readyState:'complete'},
        location: {pathname, search:'?email=private@example.test'}, URLSearchParams,
        fetch: (url, options) => {calls.push({url, options});return Promise.resolve();}
    });
    return {calls, events, allow: () => {allowed = true;}};
}
let f = fixture(false); assert.equal(f.calls.length, 0); console.log('PASS No collection without consent');
f.allow();f.events['hiddencms:privacychange'](); assert.equal(f.calls.length, 1);console.log('PASS Collection starts after consent');
f.events['hiddencms:privacychange']();assert.equal(f.calls.length, 1);console.log('PASS No duplicate on preference changes');
assert.equal(f.calls[0].options.body.get('path'), '/fr/page');assert.equal(f.calls[0].options.body.has('email'),false);console.log('PASS Query parameters not transmitted');
for(const path of ['/fr/admin/pages','/fr/user/login','/fr/files/1'])assert.equal(fixture(true,path).calls.length,0);
console.log('PASS Private pages excluded');
