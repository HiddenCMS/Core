$(function(){
	$(document).on('click', '.form-file-delete', function(){
		var $delete = $(this);
		var $field = $delete.closest('[data-file-field]');

		if (window.confirm($delete.data('confirm'))){
			$field.find('.form-file-delete-value').prop('disabled', false);
			$field.find('[data-file-preview]').remove();
		}

		return false;
	});
});
