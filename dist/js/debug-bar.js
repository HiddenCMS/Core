$(function(){
	var paddingBody = function(){
		$('body').css('padding-bottom', $('.debug-bar').outerHeight());
	};

	paddingBody();

	$('.debug-bar-tab').click(function(){
		if (!$(this).hasClass('active')){
			var tab = $(this).data('debug-bar');
			$('.debug-bar').addClass('active');
			$('.debug-bar-tab.active, .debug-bar-pane.active').removeClass('active');
			$(this).addClass('active');
			$('.debug-bar-pane[data-tab="'+tab+'"]').addClass('active');
			$.post('<?php echo url('ajax/settings/debug-bar') ?>', {tab: tab});
			paddingBody();
		}
	});

	var resizing = false;

	$('.debug-bar-resize').mousedown(function(e){
		resizing = true;

		var offset = $(window).height() - $('.debug-bar > nav').outerHeight() + e.pageY - $(this).offset().top;

		$(document).mousemove(function(e){
			if (resizing){
				var height = offset - e.clientY;

				if (height > 200){
					$('.debug-bar-content').height(height);
					paddingBody();
				}
			}
		});
	});

	$(document).mouseup(function(){
		if (resizing){
			$.post('<?php echo url('ajax/settings/debug-bar') ?>', {height: $('.debug-bar-content').innerHeight()});
			resizing = false;
		}
	});

	$('.debug-bar-close').click(function(){
		$('.debug-bar.active, .debug-bar .active').removeClass('active');
		$.post('<?php echo url('ajax/settings/debug-bar') ?>', {tab: ''});
		paddingBody();
	});

	$('.dropdown-toggle').on('click', function (e) {
		$(this).next().toggle();
	  });
	$('.dropdown-menu.keep-open').on('click', function (e) {
		e.stopPropagation();
	});

	$('.console-filter').click(function(){
		if($(this).children().hasClass('fa-square')) {
			$(this).children().toggleClass('fa-square').toggleClass('fa-check-square');
			var filter = $(this).data("filter");
			$('.row-'+ filter).show();
		}else if($(this).children().hasClass('fa-check-square')) {
			$(this).children().toggleClass('fa-check-square').toggleClass('fa-square');
			var filter = $(this).data("filter");
			$('.row-'+ filter).hide();
		}
	});
});
