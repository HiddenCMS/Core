$(function(){
	var initSortable = function(){
		if (typeof Sortable === 'undefined')
		{
			return;
		}

		$('.btn-sortable').each(function(){
			var $btn = $(this);
			var parentSelector = $btn.data('parent');
			var itemSelector = $btn.data('items');
			var $container = $btn.parents(parentSelector + ':first');
			var sameParentOnly = !!parseInt($btn.data('tree'), 10);

			if (!$container.length)
			{
				return;
			}

			var existing = Sortable.get($container.get(0));

			if (existing)
			{
				return;
			}

			var state = {
				oldIndex: -1
			};

			Sortable.create($container.get(0), {
				animation: 120,
				draggable: itemSelector,
				handle: '.btn-sortable',
				ghostClass: 'sortable-ghost',
				chosenClass: 'sortable-chosen',
				dragClass: 'sortable-drag',
				onStart: function(evt){
					state.oldIndex = $(evt.item).parent().children(itemSelector).index(evt.item);
				},
				onMove: function(evt){
					if (sameParentOnly && evt.from !== evt.to)
					{
						return false;
					}

					return true;
				},
				onEnd: function(evt){
					if (sameParentOnly && evt.from !== evt.to)
					{
						var $from = $(evt.from);
						var $siblings = $from.children(itemSelector);

						if (state.oldIndex < 0 || state.oldIndex >= $siblings.length)
						{
							$from.append(evt.item);
						}
						else
						{
							$siblings.eq(state.oldIndex).before(evt.item);
						}

						return;
					}

					var $item = $(evt.item);
					var newIndex = $item.parent().children(itemSelector).index(evt.item);

					if (state.oldIndex === newIndex || newIndex < 0)
					{
						return;
					}

					$.post($btn.data('update'), {
						id: $item.find('.btn-sortable:first').data('id'),
						position: newIndex
					});
				}
			});
		});
	};

	$('body').on('nf.load', initSortable);
	initSortable();
});
