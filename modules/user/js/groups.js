$(function(){
	$('ul.groups input[type=checkbox]').change(function(){
		if ($(this).prop('value') == 'admins' || $(this).prop('value') == 'members'){
			var value = this.value === 'admins' ? 'members' : 'admins';
			var other = $(this).closest('ul.groups').find('input[type=checkbox][value="' + value + '"]');
			other.prop('checked', !this.checked).closest('.ui.checkbox').toggleClass('checked', !this.checked);
		}
	});
});
