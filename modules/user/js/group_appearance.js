form.find('select[name$="[color]"], select[name$="[icon]"]', function(){
	var select = $(this);
	if (typeof $.fn.dropdown !== 'function') return;
	var isColor = /\[color\]$/.test(this.name);
	var dropdown = select.parent('.ui.dropdown');
	if (!dropdown.length){
		select.dropdown({fullTextSearch: true});
		dropdown = select.parent('.ui.dropdown');
	}

	function preview(value){
		if (isColor){
			var swatch = $('<span class="badge" aria-hidden="true">').css({width: '1em', height: '1em', padding: 0, marginRight: '.6em', verticalAlign: 'middle'});
			if (/^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i.test(value)) swatch.css('backgroundColor', value);
			else if (/^(default|primary|secondary|success|danger|warning|info|light|dark)$/.test(value)) swatch.addClass('badge-' + value);
			return swatch;
		}
		if (/^fa[rsb] fa-[a-z0-9-]+$/.test(value)) return $('<i aria-hidden="true">').addClass(value).css({width: '1.2em', marginRight: '.6em', textAlign: 'center'});
		return $();
	}

	function selected(){
		var text = dropdown.children('.text');
		text.children('[aria-hidden]').remove();
		text.prepend(preview(select.val() || ''));
	}

	dropdown.dropdown('setting', 'clearable', true);
	dropdown.dropdown('setting', 'placeholder', isColor ? 'Sans couleur' : 'Sans ic\u00f4ne');
	dropdown.dropdown('setting', 'onChange', selected);
	dropdown.find('.menu > .item').each(function(){
		$(this).children('[aria-hidden]').remove();
		$(this).prepend(preview($(this).attr('data-value') || ''));
	});
	selected();
});
