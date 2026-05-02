$(function(){
	var updating = false;

	var update = function(){
		if (updating){
			return;
		}

		updating = true;

		var data = {};

		(new FormData($('form')[0])).forEach(function(value, name){
			if (data[name] !== undefined){
				if (!data[name].push){
					data[name] = [data[name]];
				}

				data[name].push(value || '');
			}
			else {
				data[name] = value || '';
			}
		});

		$.post('<?php echo url('admin/ajax/statistics.json') ?>', data, function(series){
			$('#highcharts').highcharts('StockChart', {
				chart: {
					height: 690,
					zoomType: 'x'
				},
				title: {
					text: null
				},
				xAxis: {
					type: 'datetime'
				},
				yAxis: {
					title: {
						text: null
					}
				},
				legend: {
					enabled: false
				},
				credits: {
					enabled: false
				},
				rangeSelector: {
					enabled: false
				},
				series: series
			});
		}).always(function(){
			updating = false;
		});
	};

	update();
	$('form input, form select, .date').on('change changeDate dp.change', update);
});
