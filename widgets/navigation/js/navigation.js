$(function(){
	var close = function($menu, $parent){
		$menu.next('.nav').stop(true, true).slideUp(140);
		$parent.removeClass('active');
	};

	$('.nav .nav-link[data-toggle="collapse"]').each(function(){
		var $menu   = $(this);
		var $parent = $menu.parent();
		var $subnav = $menu.next('.nav');

		if ($menu.hasClass('active') || $parent.hasClass('active'))
		{
			$parent.addClass('active');
			$subnav.show();
		}
		else
		{
			$parent.removeClass('active');
			$subnav.hide();
		}

		$menu.on('click', function(e){
			e.preventDefault();

			if ($parent.hasClass('active')){
				close($menu, $parent);
			}
			else {
				$parent.addClass('active');
				$subnav.stop(true, true).slideDown(140);

				$parent.siblings('.nav-item').each(function(){
					var $sibling = $(this);
					close($sibling.children('.nav-link[data-toggle="collapse"]'), $sibling);
				});
			}
		});
	});
});
