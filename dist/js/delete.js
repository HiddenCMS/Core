$(function(){
	$('body').on('click', 'a.delete', function(e){
		e.preventDefault();

		if (typeof modal !== 'undefined' && typeof modal.load === 'function')
		{
			modal.load($(this).attr('href'));
			return false;
		}

		return false;
	});

	confirm_deletion = function(anchor){
		$.ajax({
			url: $(anchor).attr('href'),
			type: 'POST',
			data: $(anchor).attr('data-form-id')+'[]=delete',
			dataType: 'text',
			success: function(data){
				if (data == 'OK'){
					if (typeof $(anchor).parents('.alert').alert === 'function'){
						$(anchor).parents('.alert').alert('close');
					}
					else{
						$(anchor).parents('.alert').remove();
					}
					if ((table = $(anchor).parents('.alert').nextAll('.table-area')).length){
						$.ajax({
							url: window.location.pathname,
							type: 'POST',
							data: 'table_id='+$(table).attr('data-table-id'),
							dataType: 'json',
							success: function(data){
								$(table).children('.table-content').html(data.content);
							}
						});
					}
					else{
						document.location.reload();
					}
				}
				else{
					var json = $.parseJSON(data);

					if (typeof json == 'object' && typeof json.redirect != 'undefined'){
						if (window.location.pathname == json.redirect.split('#')[0]){
							window.location.href = json.redirect;
							location.reload();
						}
						else {
							window.location.href = json.redirect;
						}
					}
					else {
						$(anchor).parents('.alert').html('<button data-dismiss="alert" class="close" type="button">×</button>'+data);
					}
				}
			}
		});

		return false;
	};
});
