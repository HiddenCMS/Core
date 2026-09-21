<?php
$payload = json_encode([
	'outlineId' => (int)$outline_id,
	'isBaseOutline' => !empty($outline['base']),
	'layout'    => $layout,
	'widgets'   => $widgets,
	'types'     => $types,
	'icons'     => $icons,
	'assets'    => [
		'tinyMce'      => js('tinymce/tinymce.min.js'),
		'formTinyMce'  => js('form_tinymce.js')
	],
	'urls'      => [
		'save'    => url('admin/ajax/live-editor/layout-save'),
		'assignmentsSave' => url('admin/ajax/live-editor/assignments-save'),
		'optionsSave' => url('admin/ajax/live-editor/options-save'),
		'widgetAdmin' => url('admin/ajax/live-editor/builder-widget-admin'),
		'preview' => url()
	]
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<div class="layout-builder">
	<header class="layout-builder-header">
		<div class="layout-builder-brand">
			<?php echo icon('fas fa-layer-group') ?>
			<span><strong><?php echo $this->lang('Layout builder') ?></strong></span>
		</div>
		<label class="layout-builder-outline">
			<span><?php echo $this->lang('Outline') ?></span>
			<select id="layout-builder-outline">
				<?php foreach ($outlines as $id => $title): ?>
					<option value="<?php echo url('admin/live-editor?outline_id='.(int)$id) ?>"<?php if ((int)$id === (int)$outline_id) echo ' selected' ?>><?php echo utf8_htmlentities($title) ?></option>
				<?php endforeach ?>
			</select>
		</label>
		<div class="layout-builder-header-actions">
			<a class="ui button" href="<?php echo url('admin') ?>"><?php echo icon('fas fa-tachometer-alt').' '.$this->lang('Dashboard') ?></a>
			<a class="ui button" href="<?php echo url() ?>"><?php echo icon('fas fa-times').' '.$this->lang('Exit') ?></a>
		</div>
	</header>

	<nav class="layout-builder-tabs" aria-label="<?php echo $this->lang('Outline settings') ?>">
		<button type="button" class="active" data-builder-tab="layout"><?php echo icon('fas fa-columns').' '.$this->lang('Layout') ?></button>
		<button type="button" data-builder-tab="assignments"><?php echo icon('fas fa-route').' '.$this->lang('Assignments') ?></button>
		<button type="button" data-builder-tab="options"><?php echo icon('fas fa-sliders-h').' '.$this->lang('Options') ?></button>
	</nav>

	<div class="layout-builder-workspace">
		<main class="layout-builder-main" data-builder-panel="layout">
			<div class="layout-builder-toolbar">
				<div>
					<h1><?php echo utf8_htmlentities($outline_title) ?></h1>
					<span id="layout-builder-status"><?php echo $this->lang('No unsaved changes') ?></span>
				</div>
				<div class="layout-builder-actions">
					<button type="button" class="ui icon button" id="layout-builder-undo" title="<?php echo $this->lang('Undo') ?>" disabled><?php echo icon('fas fa-undo') ?></button>
					<button type="button" class="ui icon button" id="layout-builder-redo" title="<?php echo $this->lang('Redo') ?>" disabled><?php echo icon('fas fa-redo') ?></button>
					<a class="ui button" href="<?php echo url() ?>" target="_blank" rel="noopener"><?php echo icon('fas fa-eye').' '.$this->lang('Preview') ?></a>
					<button type="button" class="ui primary button" id="layout-builder-save" disabled><?php echo icon('fas fa-save').' '.$this->lang('Save layout') ?></button>
				</div>
			</div>
			<div id="layout-builder-canvas" class="layout-builder-canvas" aria-live="polite"></div>
		</main>

		<section class="layout-builder-main layout-builder-panel" data-builder-panel="assignments" hidden>
			<div class="layout-builder-settings-heading">
				<div><h1><?php echo $this->lang('Assignments') ?></h1><p><?php echo $this->lang('Choose the pages and reserved routes that use this outline.') ?></p></div>
				<button type="button" class="ui primary button" id="layout-builder-assignments-save"><?php echo icon('fas fa-save').' '.$this->lang('Save assignments') ?></button>
			</div>
			<form id="layout-builder-assignments" class="ui form layout-builder-settings-grid">
				<div class="layout-builder-settings-section">
					<h2><?php echo icon('far fa-file-alt').' '.$this->lang('Pages') ?></h2>
					<div class="layout-builder-check-list">
						<?php foreach ($pages as $id => $label): ?>
						<label><input type="checkbox" name="pages[]" value="<?php echo (int)$id ?>"<?php if (in_array((int)$id, $selected_pages, TRUE)) echo ' checked' ?>> <span><?php echo utf8_htmlentities($label) ?></span></label>
						<?php endforeach ?>
						<?php if (!$pages): ?><p class="layout-builder-empty-note"><?php echo $this->lang('No page is available.') ?></p><?php endif ?>
					</div>
				</div>
				<div class="layout-builder-settings-section">
					<h2><?php echo icon('fas fa-route').' '.$this->lang('Reserved routes') ?></h2>
					<div class="layout-builder-check-list">
						<?php foreach ($reserved_routes as $route => $label): ?>
						<label><input type="checkbox" name="routes[]" value="<?php echo utf8_htmlentities($route) ?>"<?php if (in_array($route, $selected_reserved_routes, TRUE)) echo ' checked' ?>> <span><?php echo utf8_htmlentities($label) ?></span></label>
						<?php endforeach ?>
						<?php if (!$reserved_routes): ?><p class="layout-builder-empty-note"><?php echo $this->lang('No reserved route is available.') ?></p><?php endif ?>
					</div>
				</div>
			</form>
			<div class="layout-builder-form-status" id="layout-builder-assignments-status" role="status"></div>
		</section>

		<section class="layout-builder-main layout-builder-panel" data-builder-panel="options" hidden>
			<div class="layout-builder-settings-heading">
				<div><h1><?php echo $this->lang('Outline options') ?></h1><p><?php echo $this->lang('Configure the identity and global behavior of this outline.') ?></p></div>
				<button type="button" class="ui primary button" id="layout-builder-options-save"><?php echo icon('fas fa-save').' '.$this->lang('Save options') ?></button>
			</div>
			<form id="layout-builder-options" class="ui form layout-builder-options-form">
				<div class="two fields">
					<div class="required field"><label><?php echo $this->lang('Title') ?></label><input type="text" name="title" value="<?php echo utf8_htmlentities(isset($outline['title']) ? $outline['title'] : '') ?>" required></div>
					<div class="required field"><label><?php echo $this->lang('Internal name') ?></label><input type="text" name="name" value="<?php echo utf8_htmlentities(isset($outline['name']) ? $outline['name'] : '') ?>" required></div>
				</div>
				<div class="field"><label><?php echo $this->lang('Theme') ?></label><select name="theme"><?php foreach ($themes as $name => $label): ?><option value="<?php echo utf8_htmlentities($name) ?>"<?php if (isset($outline['theme']) && $outline['theme'] === $name) echo ' selected' ?>><?php echo utf8_htmlentities($label) ?></option><?php endforeach ?></select></div>
				<div class="grouped fields layout-builder-option-toggles">
					<label><input type="checkbox" name="base" value="1"<?php if (!empty($outline['base'])) echo ' checked' ?>> <span><?php echo $this->lang('Default outline') ?></span></label>
					<label><input type="checkbox" name="breadcrumb" value="1"<?php if (!isset($outline['breadcrumb']) || $outline['breadcrumb']) echo ' checked' ?>> <span><?php echo $this->lang('Show breadcrumbs') ?></span></label>
					<label><input type="checkbox" name="enabled" value="1"<?php if (!isset($outline['enabled']) || $outline['enabled']) echo ' checked' ?>> <span><?php echo $this->lang('Enable this outline') ?></span></label>
				</div>
			</form>
			<div class="layout-builder-form-status" id="layout-builder-options-status" role="status"></div>
		</section>
	</div>
</div>
<div class="layout-builder-style-template" id="layout-builder-row-styles"><?php echo $styles_row ?></div>
<div class="layout-builder-style-template" id="layout-builder-widget-styles"><?php echo $styles_widget ?></div>
<div class="layout-builder-style-template" id="layout-builder-widget-form">
	<?php
	$widget_id = 0;
	$title = '';
	$widget = key($widgets);
	$type = isset($types[$widget]) ? key($types[$widget]) : 'index';
	include __DIR__.'/widget.tpl.php';
	?>
</div>
<script type="application/json" id="layout-builder-data"><?php echo $payload ?></script>
