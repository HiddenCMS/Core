$(function(){
	var init = function(){
		var $url = $('[name="url"]');
		var $picker = $('[data-menu-url-picker]');
		var $mode = $picker.find('input[name="menu_url_mode"]');
		var $select = $('#menu-front-url-select');

		if (!$url.length || !$picker.length || !$mode.length || !$select.length)
		{
			return;
		}

		var syncMode = function(forceApply){
			var mode = ($mode.filter(':checked').val() || 'custom').toString();
			var isFront = mode === 'front';
			var value = ($select.val() || '').toString();

			$picker.toggleClass('is-front', isFront).toggleClass('is-custom', !isFront);
			$select.prop('disabled', !isFront);
			$url.prop('readonly', isFront);

			if (isFront && forceApply !== false && value.length)
			{
				$url.val(value).trigger('change');
			}
		};

		$mode.off('change.menuurl').on('change.menuurl', function(){
			syncMode(true);

			if (($mode.filter(':checked').val() || '') === 'custom')
			{
				$url.trigger('focus');
			}
		});

		$select.off('change.menuurl').on('change.menuurl', function(){
			if (($mode.filter(':checked').val() || '') === 'front')
			{
				syncMode(true);
			}
		});

		if (!$mode.filter(':checked').length)
		{
			$mode.filter('[value="custom"]').prop('checked', true);
		}

		syncMode(false);

		if (($mode.filter(':checked').val() || '') === 'front')
		{
			syncMode(true);
		}
	};

	$('body').on('nf.load', init);

	init();
});
