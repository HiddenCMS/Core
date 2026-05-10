$(function(){
	var init = function(){
		var getLevel = function($row){
			var level = parseInt($row.find('.btn-menu-sort:first').data('level'), 10);
			return isNaN(level) ? 0 : level;
		};

		var collectDescendants = function($row){
			var baseLevel = getLevel($row);
			var $children = $();
			var $next = $row.next();

			while ($next.length && getLevel($next) > baseLevel)
			{
				$children = $children.add($next);
				$next = $next.next();
			}

			return $children;
		};

		var detachDescendants = function($row){
			var $children = collectDescendants($row);

			if ($children.length)
			{
				$row.data('menu-sort-children', $children);
				$children.detach();
			}
		};

		var restoreDescendants = function($row){
			var $children = $row.data('menu-sort-children');

			if ($children && $children.length)
			{
				$row.after($children);
			}

			$row.removeData('menu-sort-children');
		};

		$('.btn-menu-sort').each(function(){
			var $btn = $(this);
			var parentSelector = $btn.data('parent');
			var itemSelector = $btn.data('items');
			var $container = $btn.parents(parentSelector + ':first');
			var dragStartOrder = null;

			var collectOrder = function(){
				return $container.find(itemSelector).map(function(){
					return $(this).find('.btn-menu-sort:first').data('id');
				}).get();
			};

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
				tolerance: 'pointer',
				items: itemSelector,
				opacity: 0.6,
				revert: true,
				forcePlaceholderSize: true,
				helper: function(event, item){
					var $item = $(item);
					var $children = collectDescendants($item);
					var $helperTable = $('<table class="table table-hover table-striped"></table>');
					var $helperBody = $('<tbody></tbody>');

					$helperBody.append($item.clone());

					if ($children.length)
					{
						$children.each(function(){
							$helperBody.append($(this).clone());
						});
					}

					$helperTable.append($helperBody);

					return $('<div class="menu-sort-helper"></div>')
						.css({
							width: $container.outerWidth(),
							background: '#fff',
							border: '1px solid #d9dee3',
							boxShadow: '0 6px 18px rgba(0, 0, 0, 0.15)',
							opacity: 0.95
						})
						.append($helperTable);
				},
				start: function(event, ui){
					var $item = $(ui.item);
					var $children = collectDescendants($item);
					var totalHeight = $item.outerHeight();

					$children.each(function(){
						totalHeight += $(this).outerHeight();
					});

					dragStartOrder = collectOrder();
					detachDescendants($item);
					$(ui.placeholder).height(totalHeight);
				},
				stop: function(event, ui){
					restoreDescendants($(ui.item));

					var order = collectOrder();
					var startOrderJson = JSON.stringify(dragStartOrder || []);
					var orderJson = JSON.stringify(order);

					if (orderJson === startOrderJson)
					{
						return;
					}

					var movedId = $(ui.item).find('.btn-menu-sort:first').data('id');
					var position = $(ui.item).index();

					$.post($btn.data('update'), {
						order: order,
						order_json: orderJson,
						id: movedId,
						position: position
					});
				}
			});

			$container.data('menu-sort-bound', true);
		});
	};

	$('body').on('nf.load', init);

	init();
});
