$(function(){
	var initSortable = function(){
		if (!$.fn.sortable)
		{
			return;
		}

		$('.btn-sortable').each(function(){
			var $btn = $(this);
			var parentSelector = $btn.data('parent');
			var itemSelector = $btn.data('items');
			var directItemSelector = (itemSelector || '').replace(/^>\s*/, '');
			var $container = $btn.parents(parentSelector + ':first');

			if (!$container.length || $container.data('sortable-bound'))
			{
				return;
			}

			if ($container.data('ui-sortable'))
			{
				$container.sortable('destroy');
			}

			$container.sortable({
				axis: 'y',
				cursor: 'move',
				tolerance: 'pointer',
				items: itemSelector,
				handle: '.btn-sortable',
				opacity: 0.6,
				revert: true,
				forcePlaceholderSize: true,
				update: function(event, ui){
					$.post($btn.data('update'), {
						id: $(ui.item).find('.btn-sortable:first').data('id'),
						position: $(this).children(directItemSelector).index(ui.item)
					});
				}
			});

			$container.data('sortable-bound', true);
		});
	};

	$('body').on('nf.load', initSortable);
	initSortable();
});
