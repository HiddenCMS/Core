$(function(){
	var init = function(){
		var $url = $('[name="url"]');
		var $mode = $('[name="menu_url_mode"]');
		var $select = $('[name="menu_front_url"]');
		var $form = $url.closest('form');

		if (!$url.length || !$mode.length || !$select.length)
		{
			return;
		}

		var syncMode = function(forceApply){
			var mode = ($mode.val() || 'custom').toString();
			var isFront = mode === 'front';
			var value = ($select.val() || '').toString();
			var $dropdown = $select.closest('.ui.dropdown');

			$select.prop('disabled', !isFront);
			$dropdown.toggleClass('disabled', !isFront).attr('aria-disabled', !isFront ? 'true' : 'false');
			$url.prop('disabled', isFront);

			if (isFront && forceApply !== false && value.length)
			{
				$url.val(value).trigger('change');
			}
		};

		$mode.off('change.menuurl').on('change.menuurl', function(){
			syncMode(true);

			if (($mode.val() || '') === 'custom')
			{
				$url.trigger('focus');
			}
		});

		$select.off('change.menuurl').on('change.menuurl', function(){
			if (($mode.val() || '') === 'front')
			{
				syncMode(true);
			}
		});

		$form.off('submit.menuurl').on('submit.menuurl', function(){
			$url.prop('disabled', false);
		});

		syncMode(false);

		if (($mode.val() || '') === 'front')
		{
			syncMode(true);
		}
	};

	$('body').on('nf.load', init);

	init();
});
