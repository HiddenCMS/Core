$(function(){
	var editor = $('.user-editor');
	var tabs = editor.find('.user-editor-tabs > .item');
	if (!editor.length || typeof $.fn.tab !== 'function') return;
	var storageKey = 'user-editor-tab:' + window.location.pathname;

	function activate(name){
		tabs.each(function(){
			var selected = $(this).attr('data-tab') === name;
			$(this).attr({'aria-selected': selected ? 'true' : 'false', tabindex: selected ? '0' : '-1'});
		});
		try { window.sessionStorage.setItem(storageKey, name); } catch (e) {}
		$(window).trigger('resize');
	}

	tabs.tab({context: editor, onVisible: activate});
	var initial = 'account';
	try { initial = window.sessionStorage.getItem(storageKey) || initial; } catch (e) {}
	var errors = editor.find('.user-editor-pane').filter(function(){
		return $(this).find('.field.error, .error.message, .alert-danger').length > 0;
	}).first();
	if (errors.length) initial = errors.attr('data-tab');
	if (!tabs.filter(function(){ return $(this).attr('data-tab') === initial; }).length) initial = 'account';
	tabs.tab('change tab', initial);

	tabs.on('keydown', function(event){
		var index = tabs.index(this);
		if (event.key === 'ArrowRight') index = (index + 1) % tabs.length;
		else if (event.key === 'ArrowLeft') index = (index + tabs.length - 1) % tabs.length;
		else if (event.key === 'Home') index = 0;
		else if (event.key === 'End') index = tabs.length - 1;
		else return;
		event.preventDefault();
		tabs.eq(index).trigger('click').trigger('focus');
	});
});
