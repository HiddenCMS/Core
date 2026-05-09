$(function(){
	var init = function(){
		$('.btn-sortable').filter(function(){
			return ($(this).data('update') || '').indexOf('/admin/ajax/menu/items/sort') !== -1;
		}).each(function(){
			var $btn = $(this);
			var parentSelector = $btn.data('parent');
			var itemSelector = $btn.data('items');
			var $container = $btn.parents(parentSelector + ':first');

			if (!$container.length || $container.data('menu-sort-bound'))
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
				intersect: 'pointer',
				items: itemSelector,
				opacity: 0.6,
				revert: true,
				update: function(event, ui){
					var order = $container.find(itemSelector).map(function(){
						return $(this).find('.btn-sortable:first').data('id');
					}).get();

					$.post($btn.data('update'), {
						order: order
					});
				}
			});

			$container.data('menu-sort-bound', true);
		});
	};

	$('body').on('nf.load', init);

	init();
});
