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
$news_categories = $this->form()->value('news_categories') ?: [];

unset($modules['']);

$labels = [
	'static'        => $this->lang('Contenu statique'),
	'module'        => $this->lang('Module'),
	'module_type'   => $this->lang('Type de module'),
	'news_category' => $this->lang('Catégorie d\'actualités'),
	'add_static'    => $this->lang('Ajouter du contenu'),
	'add_module'    => $this->lang('Ajouter un module')
];

$icons = [
	'static' => icon('fas fa-align-left'),
	'module' => icon('fas fa-cube'),
	'up'     => icon('fas fa-arrow-up'),
	'down'   => icon('fas fa-arrow-down'),
	'delete' => icon('far fa-trash-alt')
];

if ($modules)
{
	$this->js_load('
		(function(){
			var modules = '.json_encode($modules, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).';
			var newsCategories = '.json_encode($news_categories, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).';
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
				return $("<option />").attr("value", value).prop("selected", String(value) == String(selected)).text(label);
			};

			var moduleSelect = function(selected){
				var $select = $("<select />").addClass("form-control form-control-sm page-block-module");

				$.each(modules, function(value, label){
					$select.append(option(value, label, selected));
				});

				return $select;
			};

			var categorySelect = function(selected){
				var $select = $("<select />").addClass("form-control form-control-sm page-block-news-category");

				$.each(newsCategories, function(value, label){
					$select.append(option(value, label, selected));
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
						blocks.push({
							type: "module",
							module: $block.find(".page-block-module").val() || "",
							news_category: $block.find(".page-block-news-category").val() || ""
						});
					}
				});

				$field.val(JSON.stringify(blocks));
			};

			var refreshBlock = function($block){
				$block.find(".page-block-news").toggle($block.find(".page-block-module").val() == "news");
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
				$body.append($content);
				$list.append($block.append($header).append($body));
				read();
			};

			var addModule = function(block){
				var $block = $("<div />").addClass("page-block card mb-2").attr("data-type", "module").data("type", "module");
				var $header = $("<div />").addClass("card-header py-2 d-flex align-items-center").append($("<strong />").text(labels.module));
				var $body = $("<div />").addClass("card-body p-2");
				var $module = moduleSelect(block && block.module ? block.module : Object.keys(modules)[0]);
				var $news = $("<div />").addClass("page-block-news mt-2")
					.append($("<label />").addClass("mb-1").text(labels.news_category))
					.append(categorySelect(block && block.news_category ? block.news_category : ""));

				$header.append(controls());
				$body.append($("<label />").addClass("mb-1").text(labels.module_type)).append($module).append($news);
				$list.append($block.append($header).append($body));
				refreshBlock($block);
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

			$composer.on("change keyup", "select, textarea", function(){
				refreshBlock($(this).closest(".page-block"));
			});

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
