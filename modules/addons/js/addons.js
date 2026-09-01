$(function(){
	var filters = {type: 'all', status: 'all'};

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
	});

	if ($.fn.dropdown){
		$('.addon-actions.ui.dropdown').dropdown({action: 'hide'});
	}
});
