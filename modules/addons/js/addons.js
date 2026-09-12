$(function(){
	var filters = {type: 'all', status: 'all'};
	var storageKey = 'hiddencms:addons:filters';

	try {
		var saved = JSON.parse(sessionStorage.getItem(storageKey) || '{}');
		$('.addons-filter').each(function(){
			var group = $(this).data('filter-group');
			$(this).find('[data-filter]').each(function(){
				if (saved && $(this).data('filter') === saved[group]){
					filters[group] = saved[group];
					$(this).addClass('active').siblings().removeClass('active');
				}
			});
		});
	} catch (e){}

	function applyFilters(){
		$('.addon-card-wrapper').each(function(){
			var $card = $(this);
			var matchesType = filters.type == 'all' || $card.is(filters.type);
			var matchesStatus = filters.status == 'all' || $card.is(filters.status);
			$card.toggleClass('is-filtered-out', !matchesType || !matchesStatus);
		});
	}

	$('.addons-filter').on('click', '[data-filter]', function(){
		var $button = $(this);
		var group = $button.closest('[data-filter-group]').data('filter-group');
		filters[group] = $button.data('filter');
		$button.addClass('active').siblings().removeClass('active');
		applyFilters();
		try {
			sessionStorage.setItem(storageKey, JSON.stringify(filters));
		} catch (e){}
	});

	applyFilters();

	if ($.fn.dropdown){
		$('.addon-actions.ui.dropdown').dropdown({action: 'hide'});
	}
});
