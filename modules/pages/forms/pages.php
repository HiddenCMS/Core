<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

$rules = [
	'title' => [
		'label'         => $this->lang('Titre de la page'),
		'value'         => $this->form()->value('title'),
		'type'          => 'text',
		'rules'         => 'required'
	],
	'subtitle' => [
		'label'         => $this->lang('Sous-titre'),
		'value'         => $this->form()->value('subtitle'),
		'type'          => 'text'
	],
	'name' => [
		'label'         => $this->lang('Chemin d\'accès'),
		'value'         => $name = $this->form()->value('name'),
		'type'          => 'text',
		'check'         => function($value, $post) use ($name){
			if (!$value)
			{
				$value = $post['title'];
			}

			$value = url_title($value);

			if ($value != $name && !HiddenCMS()->db->from('pages')->where('name', $value)->empty())
			{
				return $this->lang('Chemin d\'accès déjà utilisé');
			}
		}
	],
	'outline_id' => [
		'label'         => $this->lang('Outline'),
		'value'         => $this->form()->value('outline_id'),
		'values'        => $this->form()->value('outlines'),
		'type'          => 'select'
	],
	'blocks' => [
		'label' => $this->lang('Composition de la page'),
		'value' => $this->form()->value('blocks'),
		'type'  => 'textarea',
		'check' => function($value){
			$blocks = HB()->storage->decode($value, NULL);

			if (!is_array($blocks))
			{
				return $this->lang('Composition invalide');
			}
		}
	],
	'published' => [
		'type'    => 'checkbox',
		'checked' => ['on' => $this->form()->value('published')],
		'values'  => ['on' => $this->lang('Publier la page dès maintenant')]
	]
];

$modules = $this->form()->value('modules') ?: [];

$labels = [
	'static'      => (string)$this->lang('Contenu statique'),
	'module'      => (string)$this->lang('Module'),
	'module_type' => (string)$this->lang('Type de module'),
	'block_type'  => (string)$this->lang('Affichage'),
	'add_static'  => (string)$this->lang('Ajouter du contenu'),
	'add_module'  => (string)$this->lang('Ajouter un module')
];

$icons = [
	'static' => (string)icon('fas fa-align-left'),
	'module' => (string)icon('fas fa-cube'),
	'up'     => (string)icon('fas fa-arrow-up'),
	'down'   => (string)icon('fas fa-arrow-down'),
	'delete' => (string)icon('far fa-trash-alt')
];

if ($modules)
{
	$this->js_load('
		(function(){
			var modules = '.json_encode($modules, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).';
			var labels = '.json_encode($labels, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).';
			var icons = '.json_encode($icons, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).';
			var $field = $("[name$=\"[blocks]\"]");
			var $form = $field.closest("form");
			var $group = $field.closest(".form-group");
			var blocks = [];
			var $list;

			try {
				blocks = JSON.parse($field.val() || "[]");
			}
			catch (e) {
				blocks = [];
			}

			var option = function(value, label, selected){
				var selectedValues = $.isArray(selected) ? $.map(selected, String) : [String(selected)];

				return $("<option />").attr("value", value).prop("selected", $.inArray(String(value), selectedValues) !== -1).text(label);
			};

			var firstKey = function(object){
				for (var key in object){
					return key;
				}
				return "";
			};

			var moduleSelect = function(selected){
				var $select = $("<select />").addClass("form-control form-control-sm page-block-module");

				$.each(modules, function(value, module){
					$select.append(option(value, module.title, selected));
				});

				return $select;
			};

			var blockSelect = function(moduleName, selected){
				var $select = $("<select />").addClass("form-control form-control-sm page-block-type");
				var module = modules[moduleName] || {blocks: {}};
				var blocks = module.blocks || {};

				if (!selected || !blocks[selected]){
					selected = firstKey(blocks);
				}

				$.each(blocks, function(value, block){
					$select.append(option(value, block.title, selected));
				});

				return $select;
			};

			var read = function(){
				blocks = [];

				$list.children(".page-block").each(function(){
					var $block = $(this);

					if ($block.data("type") == "static"){
						blocks.push({
							type: "static",
							content: $block.find(".page-block-content").val() || ""
						});
					}
					else {
						var settings = {};

						$block.find(".page-block-setting").each(function(){
							if ($(this).attr("type") == "checkbox"){
								settings[$(this).data("field")] = $(this).is(":checked");
							}
							else {
								settings[$(this).data("field")] = $(this).val() || "";
							}
						});

						blocks.push({
							type: "module",
							module: $block.find(".page-block-module").val() || "",
							block: $block.find(".page-block-type").val() || "index",
							settings: settings
						});
					}
				});

				$field.val(JSON.stringify(blocks));
			};

			var renderSettings = function($block, values){
				var moduleName = $block.find(".page-block-module").val();
				var blockName = $block.find(".page-block-type").val();
				var fields = (((modules[moduleName] || {}).blocks || {})[blockName] || {}).fields || {};
				var $settings = $block.find(".page-block-settings").empty();

				$.each(fields, function(name, field){
					var $group = $("<div />").addClass("mt-2");
					var value = values && values[name] !== undefined ? values[name] : "";
					var $input;

					$group.append($("<label />").addClass("mb-1").text(field.label));

					if (field.type == "boolean" || field.type == "bool"){
						$input = $("<input />").attr("type", "checkbox").addClass("page-block-setting").attr("data-field", name).prop("checked", value === true || value == "1");
					}
					else if (field.type == "select" || field.type == "multiselect" || field.type == "multi-select"){
						$input = $("<select />").addClass("form-control form-control-sm page-block-setting").attr("data-field", name);

						if (field.type == "multiselect" || field.type == "multi-select"){
							$input.attr("multiple", "multiple");
						}

						$.each(field.values || {}, function(optionValue, optionLabel){
							$input.append(option(optionValue, optionLabel, value));
						});
					}
					else {
						$input = $("<input />").attr("type", "text").addClass("form-control form-control-sm page-block-setting").attr("data-field", name).val(value);
					}

					$settings.append($group.append($input));
				});
			};

			var refreshBlock = function($block, values){
				var moduleName = $block.find(".page-block-module").val();
				var selected = $block.find(".page-block-type").val();
				var $type = blockSelect(moduleName, selected);

				$block.find(".page-block-type").replaceWith($type);
				renderSettings($block, values || {});
				read();
			};

			var controls = function(){
				return $("<div />").addClass("btn-group btn-group-sm ml-auto")
					.append($("<button />").attr("type", "button").addClass("btn btn-light page-block-up").html(icons.up))
					.append($("<button />").attr("type", "button").addClass("btn btn-light page-block-down").html(icons.down))
					.append($("<button />").attr("type", "button").addClass("btn btn-danger page-block-delete").html(icons.delete));
			};

			var addStatic = function(block){
				var $block = $("<div />").addClass("page-block card mb-2").attr("data-type", "static").data("type", "static");
				var $header = $("<div />").addClass("card-header py-2 d-flex align-items-center").append($("<strong />").text(labels.static));
				var $body = $("<div />").addClass("card-body p-2");
				var $content = $("<textarea />").addClass("form-control page-block-content").attr("rows", 8).val(block && block.content ? block.content : "");

				$header.append(controls());
				$body	.append($("<label />").addClass("mb-1").text(labels.static))
						.append($content);
				$list.append($block.append($header).append($body));
				read();
			};

			var normalizeModuleBlock = function(block){
				block = block || {};
				block.module = block.module || firstKey(modules);
				block.settings = block.settings || {};

				block.block = block.block || "index";

				return block;
			};

			var addModule = function(block){
				block = normalizeModuleBlock(block);

				var $block = $("<div />").addClass("page-block card mb-2").attr("data-type", "module").data("type", "module");
				var $header = $("<div />").addClass("card-header py-2 d-flex align-items-center").append($("<strong />").text(labels.module));
				var $body = $("<div />").addClass("card-body p-2");
				var $module = moduleSelect(block.module);
				var $type = blockSelect(block.module, block.block);
				var $settings = $("<div />").addClass("page-block-settings");

				$header.append(controls());
				$body	.append($("<label />").addClass("mb-1").text(labels.module_type))
						.append($module)
						.append($("<label />").addClass("mb-1 mt-2").text(labels.block_type))
						.append($type)
						.append($settings);

				$list.append($block.append($header).append($body));
				renderSettings($block, block.settings);
				read();
			};

			var $composer = $("<div />").addClass("page-composer");
			var $toolbar = $("<div />").addClass("mb-2")
				.append($("<button />").attr("type", "button").addClass("btn btn-sm btn-primary mr-2 page-block-add-static").html(icons.static+" "+labels.add_static))
				.append($("<button />").attr("type", "button").addClass("btn btn-sm btn-info page-block-add-module").html(icons.module+" "+labels.add_module));

			$list = $("<div />").addClass("page-blocks");

			$group.before($composer.append($toolbar).append($list));
			$group.hide();

			if (!blocks.length){
				addStatic({content: ""});
			}
			else {
				$.each(blocks, function(i, block){
					if (block.type == "module"){
						addModule(block);
					}
					else {
						addStatic(block);
					}
				});
			}

			$composer.on("click", ".page-block-add-static", function(){
				addStatic({content: ""});
			});

			$composer.on("click", ".page-block-add-module", function(){
				addModule({type: "module"});
			});

			$composer.on("click", ".page-block-delete", function(){
				$(this).closest(".page-block").remove();
				read();
			});

			$composer.on("click", ".page-block-up", function(){
				var $block = $(this).closest(".page-block");
				$block.prev(".page-block").before($block);
				read();
			});

			$composer.on("click", ".page-block-down", function(){
				var $block = $(this).closest(".page-block");
				$block.next(".page-block").after($block);
				read();
			});

			$composer.on("change", ".page-block-module", function(){
				refreshBlock($(this).closest(".page-block"), {});
			});

			$composer.on("change", ".page-block-type", function(){
				renderSettings($(this).closest(".page-block"), {});
				read();
			});

			$composer.on("change keyup", ".page-block-content, .page-block-setting", read);

			$form.on("submit", read);
			read();
		})();
	');
}
else
{
	$this->js_load('
		(function(){
			var $field = $("[name$=\"[blocks]\"]");
			$field.closest(".form-group").hide();
			$field.val("[]");
		})();
	');
}
