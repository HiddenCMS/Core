$(function(){
	if (typeof $.fn.colorpicker == 'function'){
		$('.input-group.color').colorpicker({
			format: 'hex',
			component: '.input-group-prepend,input',
			colorSelectors: <?php echo json_encode(get_colors()) ?>
		});
	}

	var palette = <?php echo json_encode(get_colors()) ?>;

	function normalize(value){
		value = palette[value] || value;

		if (/^#[0-9a-f]{3}$/i.test(value)){
			value = '#' + value.charAt(1) + value.charAt(1)
				+ value.charAt(2) + value.charAt(2)
				+ value.charAt(3) + value.charAt(3);
		}

		return /^#[0-9a-f]{6}$/i.test(value) ? value : '#000000';
	}

	function initialize(root){
		$(root).find('.ui.input > input.color').addBack('.ui.input > input.color').each(function(){
			var input = $(this);
			var wrapper = input.closest('.ui.input');

			if (wrapper.hasClass('admin-color-field')) return;

			var picker = $('<input type="color" class="admin-color-picker" aria-label="Choisir une couleur">');
			picker.val(normalize(input.val()));
			picker.css('background-color', picker.val());

			wrapper
				.addClass('admin-color-field')
				.removeClass('left labeled')
				.children('.ui.label').remove();

			wrapper.prepend(picker);

			picker.on('input change', function(){
				picker.css('background-color', picker.val());
				input.val(picker.val()).trigger('change');
			});

			input.on('input change', function(){
				var value = input.val();

				if (palette[value] || /^#[0-9a-f]{3}([0-9a-f]{3})?$/i.test(value)){
					picker.val(normalize(value));
					picker.css('background-color', picker.val());
				}
			});
		});
	}

	initialize(document);
	$('body').on('nf.load', function(event){
		initialize(event.target || document);
	});
});
