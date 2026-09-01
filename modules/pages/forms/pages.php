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
		'value'         => $this->form()->value('name'),
		'type'          => 'text',
		'check'         => function($value, $post){
			if (!$value)
			{
				$value = $post['title'];
			}

			$value = url_title($value);
			$page_id = (int)$this->form()->value('page_id');
			$query = HiddenCMS()->db->from('pages')->where('name', $value);

			if ($page_id)
			{
				$query->where('page_id <>', $page_id);
			}

			if (!$query->empty())
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
			$blocks = HB()->storage->decode(utf8_html_entity_decode($value, ENT_QUOTES), NULL);

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
	'add_module'  => (string)$this->lang('Ajouter un module'),
	'source'      => (string)$this->lang('Source'),
	'display'     => (string)$this->lang('Affichage'),
	'configuration' => (string)$this->lang('Configuration'),
	'previous'    => (string)$this->lang('Précédent'),
	'next'        => (string)$this->lang('Suivant'),
	'cancel'      => (string)$this->lang('Annuler'),
	'confirm'     => (string)$this->lang('Valider'),
	'edit'        => (string)$this->lang('Configurer'),
	'selection'   => (string)$this->lang('Sélection actuelle')
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
	$this	->js('https://cdn.jsdelivr.net/npm/tinymce@6.8.5/tinymce.min.js')
			->js('form_tinymce');

	$this->js_load('
		(function(){
			var modules = '.json_encode($modules, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).';
			var labels = '.json_encode($labels, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).';
			var icons = '.json_encode($icons, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).';
			var $field = $("[name$=\"[blocks]\"]");
			var $form = $field.closest("form");
			var $group = $field.closest(".field, .form-group").first();
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

			var initUi = function($root){
				if ($.fn.dropdown){
					$root.find(".ui.dropdown").dropdown();
				}

				if ($.fn.checkbox){
					$root.find(".ui.checkbox").checkbox();
				}
			};

			var moduleSelect = function(selected){
				var $select = $("<select />").addClass("ui search selection dropdown page-block-module");

				$.each(modules, function(value, module){
					$select.append(option(value, module.title, selected));
				});

				return $select;
			};

			var blockSelect = function(moduleName, selected){
				var $select = $("<select />").addClass("ui search selection dropdown page-block-type");
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
				if (window.tinymce && typeof tinymce.triggerSave == "function"){
					tinymce.triggerSave();
				}

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
						var moduleBlock = $.extend(true, {}, $block.data("pageBlock") || {});
						moduleBlock.type = "module";
						blocks.push(moduleBlock);
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
					var $group = $("<div />").addClass("field");
					var value = values && values[name] !== undefined ? values[name] : "";
					var $input;

					if (field.type == "boolean" || field.type == "bool"){
						$input = $("<input />").attr("type", "checkbox").addClass("hidden page-block-setting").attr("data-field", name).prop("checked", value === true || value == "1");
						$group.append(
							$("<div />").addClass("ui checkbox")
								.append($input)
								.append($("<label />").text(field.label))
						);
					}
					else if (field.type == "select" || field.type == "multiselect" || field.type == "multi-select"){
						$input = $("<select />").addClass("ui search selection dropdown page-block-setting").attr("data-field", name);

						if (field.type == "multiselect" || field.type == "multi-select"){
							$input.attr("multiple", "multiple");
						}

						$.each(field.values || {}, function(optionValue, optionLabel){
							$input.append(option(optionValue, optionLabel, value));
						});

						$group.append($("<label />").text(field.label)).append($input);
					}
					else {
						$input = $("<input />").attr("type", "text").addClass("page-block-setting").attr("data-field", name).val(value);
						$group.append($("<label />").text(field.label)).append($input);
					}

					$settings.append($group);
				});

				initUi($settings);
			};

			var refreshBlock = function($block, values){
				var moduleName = $block.find(".page-block-module").val();
				var selected = $block.find(".page-block-type").val();
				var $type = blockSelect(moduleName, selected);

				$block.find(".page-block-type").replaceWith($type);
				renderSettings($block, values || {});
				initUi($block);
				read();
			};

			var controls = function(){
				return $("<div />").addClass("ui mini icon buttons page-block-actions")
					.append($("<button />").attr("type", "button").addClass("ui button page-block-up").html(icons.up))
					.append($("<button />").attr("type", "button").addClass("ui button page-block-down").html(icons.down))
					.append($("<button />").attr("type", "button").addClass("ui red button page-block-delete").html(icons.delete));
			};

			var addStatic = function(block){
				var $block = $("<div />").addClass("page-block ui fluid card").attr("data-type", "static").data("type", "static");
				var $header = $("<div />").addClass("content page-block-header").append($("<strong />").text(labels.static));
				var $body = $("<div />").addClass("content");
				var editorId = "page-block-editor-"+Date.now()+"-"+Math.floor(Math.random()*10000);
				var $content = $("<textarea />").addClass("wysiwyg page-block-content").attr({id: editorId, rows: 12}).val(block && block.content ? block.content : "");

				$header.append(controls());
				$body	.append($("<div />").addClass("field")
							.append($("<label />").text(labels.static))
							.append($content));
				$list.append($block.append($header).append($body));
				initUi($block);

				if (typeof form !== "undefined"){
					form.load($block, true);
				}

				read();
			};

			var normalizeModuleBlock = function(block){
				block = block || {};
				block.module = block.module || firstKey(modules);
				block.settings = block.settings || {};

				block.block = block.block || "index";

				return block;
			};

			var moduleSummary = function($block){
				var data = $block.data("pageBlock") || {};
				var module = modules[data.module] || {};
				var source = (module.blocks || {})[data.block] || {};
				var display = (source.displays || {})[(data.settings || {}).display] || {};
				var $summary = $block.find(".page-block-summary").empty();

				$summary
					.append($("<span />").append($("<small />").text(labels.module_type)).append($("<strong />").text(module.title || data.module || "-")))
					.append($("<span />").append($("<small />").text(labels.source)).append($("<strong />").text(source.title || data.block || "-")))
					.append($("<span />").append($("<small />").text(labels.display)).append($("<strong />").text(display.title || "-")));
			};

			var addModule = function(block, openWizard){
				block = normalizeModuleBlock(block);
				block.settings.display = block.settings.display || firstKey((((modules[block.module] || {}).blocks || {})[block.block] || {}).displays || {});

				var $block = $("<div />").addClass("page-block ui fluid card").attr("data-type", "module").data("type", "module");
				var $header = $("<div />").addClass("content page-block-header").append($("<strong />").text(labels.module));
				var $body = $("<div />").addClass("content page-block-module-body");
				var $summary = $("<div />").addClass("page-block-summary");
				var $edit = $("<button />").attr("type", "button").addClass("ui mini button page-block-edit").html(icons.module+" "+labels.edit);

				$header.append(controls());
				$body.append($summary).append($edit);
				$list.append($block.append($header).append($body));
				$block.data("pageBlock", $.extend(true, {}, block));
				moduleSummary($block);
				read();

				if (openWizard){
					openModuleWizard($block, true);
				}
			};

			var openModuleWizard = function($block, removeOnCancel){
				var state = $.extend(true, {}, $block.data("pageBlock") || normalizeModuleBlock({}));
				var steps = ["module", "source", "display", "configuration"];
				var stepIndex = 0;
				var stepIcons = {module: "fas fa-cube", source: "fas fa-layer-group", display: "fas fa-th-large", configuration: "fas fa-sliders-h"};
				var card = function(kind, value, title, iconClass, active){
					return $("<button />").attr({type: "button", "data-kind": kind, "data-value": value}).addClass("card page-module-choice"+(active ? " active" : ""))
						.append($("<div />").addClass("content").append($("<span />").addClass("page-module-choice-icon").append($("<i />").addClass(iconClass || "fas fa-cube"))).append($("<div />").addClass("header").text(title)));
				};
				var $modalHeader = $("<div />").addClass("header page-module-modal-header").append($("<span />").text(labels.add_module)).append($("<button />").attr({type: "button", "aria-label": labels.cancel}).addClass("page-module-close").html("&times;"));
				var $choice = $("<div />").addClass("page-module-current").attr("aria-live", "polite")
					.append($("<span />").addClass("page-module-current-icon"))
					.append($("<span />").addClass("page-module-current-copy").append($("<small />").text(labels.selection)).append($("<strong />")));
				var $modal = $("<div />").addClass("ui large modal page-module-modal")
					.append($modalHeader)
					.append($("<div />").addClass("content page-module-modal-content")
						.append($("<div />").addClass("ui mini fluid steps page-module-steps"))
						.append($choice)
						.append($("<div />").addClass("page-module-panels")))
					.append($("<div />").addClass("actions")
						.append($("<button />").attr("type", "button").addClass("ui button page-module-cancel").text(labels.cancel))
						.append($("<button />").attr("type", "button").addClass("ui button page-module-previous").text(labels.previous))
						.append($("<button />").attr("type", "button").addClass("ui primary button page-module-next").text(labels.next))
						.append($("<button />").attr("type", "button").addClass("ui primary button page-module-confirm").text(labels.confirm)));

				$.each(steps, function(i, step){
					$modal.find(".page-module-steps").append($("<button />").attr({type: "button", "data-step": step}).addClass("step").append($("<i />").addClass(stepIcons[step])).append($("<div />").addClass("content").append($("<div />").addClass("title").text(labels[step] || step))));
				});

				var currentSource = function(){
					return (((modules[state.module] || {}).blocks || {})[state.block] || {});
				};
				var defaults = function(){
					var source = currentSource();
					state.settings = state.settings || {};
					state.settings.display = state.settings.display || firstKey(source.displays || {});
					$.each(source.fields || {}, function(name, field){
						if (state.settings[name] === undefined || state.settings[name] === "") state.settings[name] = field.default;
					});
				};
				var updateCurrent = function(){
					var module = modules[state.module] || {};
					var source = currentSource();
					var display = (source.displays || {})[state.settings.display] || {};
					$choice.find(".page-module-current-icon").empty().append($("<i />").addClass(module.icon || "fas fa-cube"));
					$choice.find("strong").text($.grep([module.title, source.title, display.title], function(value){ return !!value; }).join(" · "));
				};

				var buildPanel = function(step){
					var source = currentSource();
					var $panel = $("<div />").addClass("page-module-panel").attr("data-step", step);

					if (step === "module"){
						var $cards = $("<div />").addClass("ui cards page-module-cards");
						$.each(modules, function(name, module){ $cards.append(card("module", name, module.title, module.icon, name === state.module)); });
						$panel.append($cards);
					}
					else if (step === "source"){
						var $cards = $("<div />").addClass("ui cards page-module-cards");
						$.each((modules[state.module] || {}).blocks || {}, function(name, item){ $cards.append(card("source", name, item.title, item.icon, name === state.block)); });
						$panel.append($cards);
					}
					else if (step === "display"){
						var $cards = $("<div />").addClass("ui cards page-module-cards");
						$.each(source.displays || {}, function(name, item){ $cards.append(card("display", name, item.title, item.icon, name === state.settings.display)); });
						$panel.append($cards);
					}
					else {
						var $fields = $("<div />").addClass("ui form");
						$.each(source.fields || {}, function(name, field){
							var attrs = {type: field.type === "number" ? "number" : "text", "data-setting": name};
							if (field.min !== null) attrs.min = field.min;
							if (field.max !== null) attrs.max = field.max;
							if (field.step !== null) attrs.step = field.step;
							$fields.append($("<div />").addClass("field").append($("<label />").text(field.label)).append($("<input />").attr(attrs).val(state.settings[name])));
						});
						$panel.append($fields.children().length ? $fields : $("<div />").addClass("ui message").text(labels.configuration));
					}

					return $panel;
				};

				var showStep = function(index, animate){
					var previousIndex = stepIndex;
					stepIndex = Math.max(0, Math.min(steps.length - 1, index));
					var $panels = $modal.find(".page-module-panels");
					var $current = $panels.children(".page-module-panel").first();
					var $next = buildPanel(steps[stepIndex]);

					updateCurrent();
					$modal.find(".page-module-steps .step").removeClass("active completed").each(function(i){
						$(this).toggleClass("active", i === stepIndex).toggleClass("completed", i < stepIndex);
					});

					if (!$current.length || !animate){
						$current.remove();
						$panels.empty().append($next);
					}
					else {
						var direction = stepIndex >= previousIndex ? 1 : -1;
						var currentHeight = $current.outerHeight(true);
						$next.css({display: "block", left: 0, opacity: 0, position: "absolute", top: 0, transform: "translateX("+(direction * 14)+"px)", width: "100%"});
						$panels.append($next).css("height", currentHeight);
						var nextHeight = $next.outerHeight(true);
						$current.css({left: 0, position: "absolute", top: 0, width: "100%"});
						$panels.stop(true).animate({height: nextHeight}, 220);
						window.requestAnimationFrame(function(){
							$current.css({opacity: 0, transform: "translateX("+(-direction * 10)+"px)"});
							$next.css({opacity: 1, transform: "translateX(0)"});
						});
						window.setTimeout(function(){
							$current.remove();
							$next.css({position: "relative", top: "auto", left: "auto", width: "auto"});
							$panels.css("height", "auto");
						}, 230);
					}

					$modal.find(".page-module-previous").toggle(stepIndex > 0);
					$modal.find(".page-module-next").toggle(stepIndex < steps.length - 1);
					$modal.find(".page-module-confirm").toggle(stepIndex === steps.length - 1);
				};

				$modal.on("click", ".page-module-choice", function(){
					var kind = $(this).data("kind");
					var value = String($(this).data("value"));
					if (kind === "module" && state.module !== value){ state.module = value; state.block = firstKey((modules[value] || {}).blocks || {}); state.settings = {}; }
					if (kind === "source" && state.block !== value){ state.block = value; state.settings = {}; }
					if (kind === "display") state.settings.display = value;
					defaults();
					showStep(stepIndex, false);
				});
				$modal.on("change input", "[data-setting]", function(){ state.settings[$(this).data("setting")] = $(this).val(); });
				$modal.on("click", ".page-module-next", function(){ if (stepIndex < steps.length - 1) showStep(stepIndex + 1, true); });
				$modal.on("click", ".page-module-previous", function(){ if (stepIndex > 0) showStep(stepIndex - 1, true); });
				$modal.on("click", ".page-module-steps .step", function(){ showStep(steps.indexOf($(this).data("step")), true); });
				$modal.on("click", ".page-module-confirm", function(){ defaults(); $block.data("pageBlock", $.extend(true, {}, state)); moduleSummary($block); read(); $modal.modal("hide"); });
				$modal.on("click", ".page-module-cancel", function(){ if (removeOnCancel) $block.remove(); $modal.modal("hide"); });
				$modal.on("click", ".page-module-close", function(){ if (removeOnCancel) $block.remove(); $modal.modal("hide"); });
				$modal.modal({autofocus: false, closable: false, onHidden: function(){ $modal.remove(); }}).modal("show");
				defaults();
				showStep(0, false);
			};

			var $composer = $("<div />").addClass("page-composer ui form");
			var $toolbar = $("<div />").addClass("ui mini buttons page-composer-toolbar")
				.append($("<button />").attr("type", "button").addClass("ui primary button page-block-add-static").html(icons.static+" "+labels.add_static))
				.append($("<button />").attr("type", "button").addClass("ui teal button page-block-add-module").html(icons.module+" "+labels.add_module));

			$list = $("<div />").addClass("page-blocks");

			if ($group.length){
				$group.before($composer.append($toolbar).append($list));
				$group.hide();
			}
			else {
				$field.before($composer.append($toolbar).append($list));
				$field.hide();
			}

			if (!blocks.length){
				addStatic({content: ""});
			}
			else {
				$.each(blocks, function(i, block){
					if (block.type == "module"){
						addModule(block, false);
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
				addModule({type: "module"}, true);
			});

			$composer.on("click", ".page-block-edit", function(){
				openModuleWizard($(this).closest(".page-block"), false);
			});

			$composer.on("click", ".page-block-delete", function(){
				var $block = $(this).closest(".page-block");
				var editorId = $block.find("textarea.wysiwyg").attr("id");

				if (editorId && window.tinymce && tinymce.get(editorId)){
					tinymce.get(editorId).remove();
				}

				$block.remove();
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
			var $group = $field.closest(".field, .form-group").first();

			if ($group.length){
				$group.hide();
			}
			else {
				$field.hide();
			}

			$field.val("[]");
		})();
	');
}

$layout_labels = [
	'information' => (string)$this->lang('Informations'),
	'content'     => (string)$this->lang('Contenu')
];

$layout_icons = [
	'information' => (string)icon('fas fa-info-circle'),
	'content'     => (string)icon('fas fa-align-left')
];

$this->js_load('
	(function(){
		var labels = '.json_encode($layout_labels, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).';
		var icons = '.json_encode($layout_icons, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).';
		var $title = $("[name$=\"[title]\"]").first();
		var $form = $title.closest("form");

		if (!$form.length || $form.find(".pages-form-grid").length){
			return;
		}

		var field = function(name){
			return $form.find("[name$=\"["+name+"]\"]").first().closest(".field, .form-group").first();
		};

		var heading = function(icon, label){
			return $("<div />").addClass("pages-form-column-heading")
				.append($("<span />").addClass("pages-form-column-icon").html(icon))
				.append($("<strong />").text(label));
		};

		var $grid = $("<div />").addClass("ui stackable grid pages-form-grid");
		var $information = $("<div />").attr("class", "sixteen wide mobile five wide computer column pages-form-information")
			.append(heading(icons.information, labels.information));
		var $content = $("<div />").attr("class", "sixteen wide mobile eleven wide computer column pages-form-content")
			.append(heading(icons.content, labels.content));

		$.each(["title", "subtitle", "name", "outline_id", "published"], function(i, name){
			var $field = field(name);

			if ($field.length){
				$information.append($field);
			}
		});

		var $composer = $form.find(".page-composer").first();
		var $blocks = field("blocks");

		if ($composer.length){
			$content.append($composer);
		}

		if ($blocks.length){
			$content.append($blocks);
		}

		$grid.append($information).append($content);
		$form.prepend($grid);
	})();
');
