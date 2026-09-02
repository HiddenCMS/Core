$(function(){
	$('.theme-customizer').each(function(){
		var $customizer = $(this);
		var $tabs = $customizer.find('[data-tab-target]');
		var $panes = $customizer.find('[data-tab]');

		var activate = function(name, updateHash){
			var $tab = $tabs.filter('[data-tab-target="'+name+'"]');
			var $pane = $panes.filter('[data-tab="'+name+'"]');

			if (!$tab.length || !$pane.length)
			{
				name = 'dashboard';
				$tab = $tabs.filter('[data-tab-target="dashboard"]');
				$pane = $panes.filter('[data-tab="dashboard"]');
			}

			$tabs.removeClass('active').attr('aria-selected', 'false');
			$tab.addClass('active').attr('aria-selected', 'true');
			$panes.removeClass('active').attr('hidden', true);
			$pane.removeAttr('hidden').addClass('active');

			if (updateHash && window.location.hash !== '#'+name)
			{
				history.replaceState(null, '', '#'+name);
			}
		};

		$tabs.on('click', function(event){
			event.preventDefault();
			activate($(this).data('tab-target'), true);
		});

		$(window).on('hashchange.themeCustomizer', function(){
			activate(window.location.hash.substring(1), false);
		});

		activate(window.location.hash.substring(1), false);
	});
});
