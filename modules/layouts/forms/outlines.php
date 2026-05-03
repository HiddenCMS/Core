<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

$name = $this->form()->value('name');

$rules = [
	'title' => [
		'label' => $this->lang('Titre'),
		'value' => $this->form()->value('title'),
		'type'  => 'text',
		'rules' => 'required'
	],
	'name' => [
		'label' => $this->lang('Nom technique'),
		'value' => $name,
		'type'  => 'text',
		'check' => function($value, $post) use ($name){
			if (!$value)
			{
				$value = $post['title'];
			}

			$value = url_title($value);

			if ($value != $name && !HiddenCMS()->db->from('layouts_outlines')->where('name', $value)->empty())
			{
				return $this->lang('Nom technique dÃ©jÃ  utilisÃ©');
			}
		}
	],
	'theme' => [
		'label'  => $this->lang('ThÃ¨me'),
		'value'  => $this->form()->value('theme'),
		'values' => $this->form()->value('themes'),
		'type'   => 'select',
		'rules'  => 'required'
	],
	'layout' => [
		'label' => $this->lang('Layout'),
		'value' => $this->form()->value('layout'),
		'type'  => 'textarea',
		'check' => function($value){
			if (!is_array(HB()->storage->decode($value, NULL)))
			{
				return $this->lang('Layout invalide');
			}
		}
	],
	'base' => [
		'type'    => 'checkbox',
		'checked' => ['on' => $this->form()->value('base')],
		'values'  => ['on' => $this->lang('Outline de base')]
	],
	'enabled' => [
		'type'    => 'checkbox',
		'checked' => ['on' => $this->form()->value('enabled')],
		'values'  => ['on' => $this->lang('Activer cet outline')]
	]
];

$regions = $this->form()->value('regions') ?: [];
$widgets = $this->form()->value('widgets') ?: [];
$modules = $this->form()->value('modules') ?: [];

$labels = [
	'add_row'      => (string)$this->lang('Ajouter une ligne'),
	'add_column'   => (string)$this->lang('Ajouter une colonne'),
	'add_item'     => (string)$this->lang('Ajouter une particle'),
	'empty_region' => (string)$this->lang('Aucune ligne dans cette rÃ©gion'),
	'empty_column' => (string)$this->lang('Aucune particle dans cette colonne'),
	'row_style'    => (string)$this->lang('Style de ligne'),
	'column_size'  => (string)$this->lang('Taille'),
	'type'         => (string)$this->lang('Type'),
	'widget'       => (string)$this->lang('Widget'),
	'module'       => (string)$this->lang('Module'),
	'route'        => (string)$this->lang('Route'),
	'content'      => (string)$this->lang('Contenu'),
	'style'        => (string)$this->lang('Style'),
	'delete'       => (string)$this->lang('Supprimer'),
	'page_content' => (string)$this->lang('Contenu de page'),
	'static'       => (string)$this->lang('HTML statique')
];

$icons = [
	'add'          => (string)icon('fas fa-plus'),
	'delete'       => (string)icon('far fa-trash-alt'),
	'row'          => (string)icon('fas fa-grip-lines'),
	'column'       => (string)icon('fas fa-columns'),
	'particle'     => (string)icon('fas fa-cube'),
	'page_content' => (string)icon('far fa-file-alt'),
	'widget'       => (string)icon('fas fa-puzzle-piece'),
	'module'       => (string)icon('fas fa-cube'),
	'static'       => (string)icon('fas fa-align-left')
];

$this->js_load('
	(function(){
		var regions = '.json_encode($regions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).';
		var widgets = '.json_encode($widgets, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).';
		var modules = '.json_encode($modules, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).';
		var labels = '.json_encode($labels, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).';
		var icons = '.json_encode($icons, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).';
		var $field = $("[name$=\"[layout]\"]");
		var $form = $field.closest("form");
		var $group = $field.closest(".form-group");
		var layout = {};
		var $builder = $("<div />").addClass("layout-builder");

		try {
			layout = JSON.parse($field.val() || "{}");
		}
		catch (e) {
			layout = {};
		}

		var itemTypes = {
			page_content: labels.page_content,
			widget: labels.widget,
			module: labels.module,
			static: labels.static
		};

		var columnSizes = {
			"col-12": "12 / 12",
			"col-9": "9 / 12",
			"col-8": "8 / 12",
			"col-6": "6 / 12",
			"col-4": "4 / 12",
			"col-3": "3 / 12"
		};
		var columnSizeClasses = Object.keys(columnSizes).join(" ");

		var option = function(value, label, selected){
			return $("<option />").attr("value", value).prop("selected", String(value) == String(selected)).text(label);
		};

		var select = function(values, selected, className){
			var $select = $("<select />").addClass("form-control form-control-sm "+className);

			$.each(values, function(value, label){
				$select.append(option(value, label, selected));
			});

			return $select;
		};

		var firstKey = function(object){
			for (var key in object){
				return key;
			}
			return "";
		};

		var controls = function(className){
			return $("<button />").attr("type", "button").addClass("btn btn-sm btn-outline-danger "+className).html(icons.delete);
		};

		var empty = function(className, label){
			return $("<div />").addClass("layout-empty "+className).text(label);
		};

		var refreshEmptyStates = function(){
			$builder.find(".layout-region").each(function(){
				var $rows = $(this).children(".layout-rows");
				$rows.children(".layout-region-empty").toggle(!$rows.children(".layout-row").length);
			});

			$builder.find(".layout-column").each(function(){
				var $items = $(this).find("> .card-body > .layout-items");
				$items.children(".layout-column-empty").toggle(!$items.children(".layout-item").length);
			});
		};

		var itemIcon = function(type){
			return icons[type] || icons.particle;
		};

		var itemSummary = function($item){
			var type = $item.find(".layout-item-type").val();

			if (type == "widget"){
				return widgets[$item.find(".layout-item-widget").val()] || labels.widget;
			}
			else if (type == "module"){
				var moduleName = $item.find(".layout-item-module").val();
				var route = $item.find(".layout-item-route").val();
				return (modules[moduleName] || labels.module)+(route ? " / "+route : "");
			}
			else if (type == "static"){
				return labels.static;
			}

			return labels.page_content;
		};

		var read = function(){
			var output = {};

			$builder.find(".layout-region").each(function(){
				var $region = $(this);
				var region = $region.data("region");
				output[region] = [];

				$region.children(".layout-rows").children(".layout-row").each(function(){
					var $row = $(this);
					var row = {
						style: $row.find("> .card-header .layout-row-style").val() || "",
						columns: []
					};

					$row.find("> .card-body > .layout-columns > .layout-column").each(function(){
						var $column = $(this);
						var column = {
							size: $column.find("> .card-header .layout-column-size").val() || "col-12",
							items: []
						};

						$column.find("> .card-body > .layout-items > .layout-item").each(function(){
							var $item = $(this);
							var type = $item.find(".layout-item-type").val();
							var item = {
								type: type,
								style: $item.find(".layout-item-style").val() || ""
							};

							if (type == "widget"){
								item.widget_id = $item.find(".layout-item-widget").val() || "";
							}
							else if (type == "module"){
								item.module = $item.find(".layout-item-module").val() || "";
								item.route = $item.find(".layout-item-route").val() || "";
							}
							else if (type == "static"){
								item.content = $item.find(".layout-item-content").val() || "";
							}

							column.items.push(item);
						});

						row.columns.push(column);
					});

					output[region].push(row);
				});
			});

			$field.val(JSON.stringify(output));
			refreshEmptyStates();
		};

		var refreshItem = function($item, item){
			item = item || {};
			var type = $item.find(".layout-item-type").val();
			var $settings = $item.find(".layout-item-settings").empty();
			var $summary = $item.find(".layout-item-summary");
			var $icon = $item.find(".layout-item-icon");

			$settings.append($("<label />").addClass("mb-1 mt-2").text(labels.style));
			$settings.append($("<input />").attr("type", "text").addClass("form-control form-control-sm layout-item-style").val(item.style || ""));

			if (type == "widget"){
				$settings.append($("<label />").addClass("mb-1 mt-2").text(labels.widget));
				$settings.append(select(widgets, item.widget_id || firstKey(widgets), "layout-item-widget"));
			}
			else if (type == "module"){
				$settings.append($("<label />").addClass("mb-1 mt-2").text(labels.module));
				$settings.append(select(modules, item.module || firstKey(modules), "layout-item-module"));
				$settings.append($("<label />").addClass("mb-1 mt-2").text(labels.route));
				$settings.append($("<input />").attr("type", "text").addClass("form-control form-control-sm layout-item-route").val(item.route || ""));
			}
			else if (type == "static"){
				$settings.append($("<label />").addClass("mb-1 mt-2").text(labels.content));
				$settings.append($("<textarea />").addClass("form-control form-control-sm layout-item-content").attr("rows", 4).val(item.content || ""));
			}

			$icon.html(itemIcon(type));
			$summary.text(itemSummary($item));
		};

		var addItem = function($items, item){
			item = item || {type: "page_content"};

			var $item = $("<div />").addClass("layout-item mb-2");
			var $header = $("<div />").addClass("layout-item-header");
			var $type = select(itemTypes, item.type || "page_content", "layout-item-type");
			var $settings = $("<div />").addClass("layout-item-settings");
			var $icon = $("<span />").addClass("layout-item-icon").html(itemIcon(item.type || "page_content"));
			var $summary = $("<span />").addClass("layout-item-summary");

			$header.append($icon).append($("<label />").addClass("mb-0 mr-1").text(labels.type)).append($type).append($summary).append(controls("layout-item-delete"));
			$item.append($header).append($settings);
			$items.append($item);
			refreshItem($item, item);
			read();
		};

		var addColumn = function($columns, column){
			column = column || {size: "col-12", items: []};

			var size = column.size || "col-12";
			var $column = $("<div />").addClass("layout-column card mb-3 "+size);
			var $header = $("<div />").addClass("card-header py-2 d-flex align-items-center");
			var $body = $("<div />").addClass("card-body p-2");
			var $items = $("<div />").addClass("layout-items");

			$header.append($("<span />").addClass("mr-1").html(icons.column)).append($("<label />").addClass("mb-0 mr-2").text(labels.column_size)).append(select(columnSizes, size, "layout-column-size")).append(controls("layout-column-delete").addClass("ml-auto"));
			$items.append(empty("layout-column-empty", labels.empty_column));
			$body.append($items).append($("<button />").attr("type", "button").addClass("btn btn-sm btn-outline-primary layout-item-add").html(icons.add+" "+labels.add_item));
			$columns.append($column.append($header).append($body));

			$.each(column.items || [], function(i, item){
				addItem($items, item);
			});

			read();
		};

		var addRow = function($rows, row){
			row = row || {style: "", columns: [{size: "col-12", items: []}]};

			var $row = $("<div />").addClass("layout-row card mb-3");
			var $header = $("<div />").addClass("card-header py-2 d-flex align-items-center");
			var $body = $("<div />").addClass("card-body p-2");
			var $columns = $("<div />").addClass("layout-columns row");

			$header.append($("<span />").addClass("mr-1").html(icons.row)).append($("<label />").addClass("mb-0 mr-2").text(labels.row_style)).append($("<input />").attr("type", "text").addClass("form-control form-control-sm layout-row-style").val(row.style || "")).append(controls("layout-row-delete").addClass("ml-2"));
			$body.append($columns).append($("<button />").attr("type", "button").addClass("btn btn-sm btn-outline-primary layout-column-add").html(icons.add+" "+labels.add_column));
			$rows.append($row.append($header).append($body));

			$.each(row.columns || [], function(i, column){
				addColumn($columns, column);
			});

			read();
		};

		var addRegion = function(region, title, rows){
			var $region = $("<section />").addClass("layout-region mb-3").attr("data-region", region).data("region", region);
			var $header = $("<div />").addClass("layout-region-header");
			var $rows = $("<div />").addClass("layout-rows");

			$header.append($("<h4 />").addClass("layout-region-title").text(title)).append($("<code />").addClass("layout-region-code").text(region));
			$rows.append(empty("layout-region-empty", labels.empty_region));
			$region.append($header).append($rows).append($("<button />").attr("type", "button").addClass("btn btn-sm btn-primary layout-row-add").html(icons.add+" "+labels.add_row));
			$builder.append($region);

			$.each(rows || [], function(i, row){
				addRow($rows, row);
			});
		};

		$.each(regions, function(region, title){
			addRegion(region, title, layout[region] || []);
		});

		$group.before($builder);
		$group.hide();

		$builder.on("click", ".layout-row-add", function(){
			addRow($(this).siblings(".layout-rows"));
		});

		$builder.on("click", ".layout-column-add", function(){
			addColumn($(this).siblings(".layout-columns"));
		});

		$builder.on("click", ".layout-item-add", function(){
			addItem($(this).siblings(".layout-items"));
		});

		$builder.on("click", ".layout-row-delete, .layout-column-delete, .layout-item-delete", function(){
			$(this).closest(".layout-row, .layout-column, .layout-item").remove();
			read();
		});

		$builder.on("change", ".layout-item-type", function(){
			refreshItem($(this).closest(".layout-item"), {});
			read();
		});

		$builder.on("change", ".layout-column-size", function(){
			var $column = $(this).closest(".layout-column");
			$column.removeClass(columnSizeClasses).addClass($(this).val() || "col-12");
			read();
		});

		$builder.on("change keyup", ".layout-item-widget, .layout-item-module, .layout-item-route", function(){
			var $item = $(this).closest(".layout-item");
			$item.find(".layout-item-summary").text(itemSummary($item));
			read();
		});

		$builder.on("change keyup", "input, select, textarea", read);

		$form.on("submit", read);
		read();
	})();
');
