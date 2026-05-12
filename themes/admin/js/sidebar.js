$(document).ready(function(){
	var $sidebar = $('#sidebar');
	var $body = $('body');
	var $themeToggle = $('#themeToggle');

	var isMobile = function(){
		return window.matchMedia('(max-width: 992px)').matches;
	};

	var openMobileSidebar = function(){
		$sidebar.addClass('active');
		$body.addClass('hb-no-scroll');
	};

	var closeMobileSidebar = function(){
		$sidebar.removeClass('active');
		$body.removeClass('hb-no-scroll');
	};

	var applyTheme = function(theme){
		var isLight = theme === 'light';
		$body.toggleClass('hb-theme-light', isLight);

		if ($themeToggle.length)
		{
			$themeToggle.attr('aria-pressed', isLight ? 'true' : 'false');
			$themeToggle.find('.hb-topbar-theme-icon').html(isLight ? '<i class="fas fa-lightbulb"></i>' : '<i class="far fa-lightbulb"></i>');
		}
	};

	try
	{
		applyTheme(localStorage.getItem('hb-theme') || 'dark');
	}
	catch (e)
	{
		applyTheme('dark');
	}

	$(document).on('click', '#sidebarCollapse', function(){
		if (isMobile())
		{
			if ($sidebar.hasClass('active'))
			{
				closeMobileSidebar();
			}
			else
			{
				openMobileSidebar();
			}
		}
		else
		{
			$sidebar.toggleClass('hb-collapsed');
		}
	});

	$(document).on('click', '#sidebarOverlay, #sidebarClose', function(){
		closeMobileSidebar();
	});

	$(document).on('click', '#themeToggle', function(){
		var nextTheme = $body.hasClass('hb-theme-light') ? 'dark' : 'light';
		applyTheme(nextTheme);
		try
		{
			localStorage.setItem('hb-theme', nextTheme);
		}
		catch (e){}
	});

	$(document).on('keydown', function(event){
		if (event.key === 'Escape' || event.keyCode === 27)
		{
			closeMobileSidebar();
		}
	});

	$(window).on('resize', function(){
		if (!isMobile())
		{
			$sidebar.removeClass('active');
			$body.removeClass('hb-no-scroll');
		}
		else
		{
			$sidebar.removeClass('active');
			$body.removeClass('hb-no-scroll');
		}
	});

});
