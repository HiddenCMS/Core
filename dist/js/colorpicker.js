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

	$('.ui.input.color, .ui.input > input.color').each(function(){
		var element = $(this);
		var wrapper = element.hasClass('ui') ? element : element.closest('.ui.input');
		var input = element.is('input') ? element : wrapper.children('input[type="text"]');
		if (!input.length || wrapper.children('.admin-color-picker').length) return;

		var picker = $('<input type="color" class="admin-color-picker" aria-label="Choisir une couleur">');
		picker.val(normalize(input.val()));
		wrapper.children('i.icon').replaceWith(picker);

		picker.on('input change', function(){
			input.val(picker.val()).trigger('change');
		});

		input.on('input change', function(){
			picker.val(normalize(input.val()));
		});
	});
});
