const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const root = path.join(__dirname, '..');
let initialize;
let settings;
let wrapped;
const calendar = {
  length: 1,
  closest: () => ({length: 0}),
  calendar: value => { settings = value; }
};
const field = {
  length: 1,
  wrap: () => { wrapped = true; },
  parent: () => calendar
};
const $ = value => value;
$.fn = {calendar: () => {}};
const context = vm.createContext({$, form: {find: (selector, callback) => { initialize = callback; }}});
for (const file of ['dist/js/bootstrap-datetimepicker/moment.min.js', 'dist/js/bootstrap-datetimepicker/locales/fr.js', 'dist/js/form_calendar.js']) {
  vm.runInContext(fs.readFileSync(path.join(root, file), 'utf8'), context);
}
function mount(type, format, disabled = false, existing = false) {
  settings = undefined;
  wrapped = false;
  initialize.call({
    is: () => disabled,
    attr: name => ({'data-calendar-type': type, 'data-calendar-format': format, 'data-calendar-locale': 'fr'})[name],
    closest: selector => selector === '.ui.calendar' ? {length: existing ? 1 : 0} : field
  });
}
mount('date', 'L');
assert.equal(wrapped, true);
assert.equal(settings.type, 'date');
assert.equal(settings.firstDayOfWeek, 1);
assert.equal(settings.text.months[8], 'septembre');
assert.equal(settings.formatter.date(settings.parser.date('15/06/1990')), '15/06/1990');
assert.equal(settings.formatter.date(settings.parser.date('29/02/2024')), '29/02/2024');
assert.equal(settings.parser.date('29/02/2023'), null);
assert.equal(settings.parser.date('31/04/2024'), null);
assert.equal(settings.parser.date(''), null);
assert.equal(settings.formatter.date(null), '');
mount('datetime', 'L LT');
assert.equal(settings.formatter.datetime(settings.parser.date('15/06/1990 14:30')), '15/06/1990 14:30');
mount('time', 'LT');
assert.equal(settings.formatter.time(settings.parser.date('14:30')), '14:30');
assert.equal(settings.parser.date('25:30'), null);
mount('date', 'L', true);
assert.equal(wrapped, false);
assert.equal(settings, undefined);
mount('date', 'L', false, true);
assert.equal(wrapped, false);
assert.equal(settings, undefined);
console.log('Calendar initialization, French formats, parsing, empty/disabled fields and repeated loads passed.');
