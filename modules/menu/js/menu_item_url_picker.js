$(function(){
	var init = function(){
		var $url = $('[name="url"]');
		var $modal = $('#menu-url-picker-modal');
		var $select = $('#menu-front-url-select');
		var $apply = $('#menu-front-url-apply');
		var $custom = $('#menu-custom-url-mode');

		if (!$url.length || !$modal.length)
		{
			return;
		}

		$apply.off('click.menuurl').on('click.menuurl', function(){
			var value = ($select.val() || '').toString();

			if (!value.length)
			{
				$select.trigger('focus');
				return;
			}

			$url.val(value).trigger('change');
			$modal.modal('hide');
		});

		$custom.off('click.menuurl').on('click.menuurl', function(){
			$modal.modal('hide');
			$url.trigger('focus');
		});

		$modal.off('shown.bs.modal.menuurl').on('shown.bs.modal.menuurl', function(){
			$select.trigger('focus');
		});
	};

	$('body').on('nf.load', init);

	init();
});
