form.find('input[data-calendar-type]', function(){
	var input = $(this);
	if (input.is(':disabled, [readonly]') || typeof $.fn.calendar !== 'function' || typeof moment !== 'function') return;
	if (input.closest('.ui.calendar').length) return;

	var localeName = input.attr('data-calendar-locale');
	var locale = moment.localeData(localeName);
	var format = input.attr('data-calendar-format');
	var field = input.closest('.ui.input');
	if (!field.length) field = input;
	field.wrap('<div class="ui calendar"></div>');
	var calendar = field.parent();
	var modal = calendar.closest('.ui.modal');

	function printDate(date){
		return date ? moment(date).locale(localeName).format(format) : '';
	}

	function names(method, count, unit){
		var values = [];
		for (var index = 0; index < count; index++){
			values.push(locale[method](moment([2000, 0, 2])[unit](index), ''));
		}
		return values;
	}

	calendar.calendar({
		type: input.attr('data-calendar-type'),
		firstDayOfWeek: locale.firstDayOfWeek(),
		context: modal.length ? modal : $('body'),
		popupOptions: {position: 'bottom left'},
		text: {
			days: names('weekdaysMin', 7, 'day'),
			dayNamesShort: names('weekdaysShort', 7, 'day'),
			dayNames: names('weekdays', 7, 'day'),
			months: names('months', 12, 'month'),
			monthsShort: names('monthsShort', 12, 'month')
		},
		formatter: {
			date: function(date){ return date ? moment(date).locale(localeName).format('L') : ''; },
			time: function(date){ return date ? moment(date).locale(localeName).format('LT') : ''; },
			datetime: printDate,
			cellTime: function(date){ return moment(date).locale(localeName).format('LT'); }
		},
		parser: {
			date: function(text){
				var date = moment(text, format, localeName, true);
				return date.isValid() ? date.toDate() : null;
			}
		}
	});
});
