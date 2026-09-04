$(function(){
	var type = $('select[name="type"]');
	function updateOptions(){
		$('textarea[name="options"]').closest('.field').toggle(['select', 'radio', 'checkbox'].indexOf(type.val()) !== -1);
	}
	type.on('change', updateOptions);
	updateOptions();
});
