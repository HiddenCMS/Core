<?php
$payload = json_encode([
	'outlineId' => (int)$outline_id,
	'layout'    => $layout,
	'widgets'   => $widgets,
	'types'     => $types,
	'icons'     => $icons,
	'urls'      => [
		'save'    => url('admin/ajax/live-editor/layout-save'),
		'widgetAdmin' => url('admin/ajax/live-editor/builder-widget-admin'),
		'preview' => url(),
		'legacy'  => url('admin/live-editor?outline_id='.(int)$outline_id.'&legacy=1')
	]
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$outline_edit_url = $outline_id !== NULL
	? url('admin/outlines/'.(int)$outline_id.'/'.url_title($outline_title))
	: url('admin/outlines');
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
		<a class="active" href="<?php echo url('admin/live-editor?outline_id='.(int)$outline_id) ?>"><?php echo icon('fas fa-columns').' '.$this->lang('Layout') ?></a>
		<a href="<?php echo $outline_edit_url ?>"><?php echo icon('fas fa-route').' '.$this->lang('Assignments') ?></a>
		<a href="<?php echo $outline_edit_url ?>"><?php echo icon('fas fa-sliders-h').' '.$this->lang('Options') ?></a>
	</nav>

	<div class="layout-builder-workspace">
		<main class="layout-builder-main">
			<div class="layout-builder-toolbar">
				<div>
					<h1><?php echo utf8_htmlentities($outline_title) ?></h1>
					<span id="layout-builder-status"><?php echo $this->lang('No unsaved changes') ?></span>
				</div>
				<div class="layout-builder-actions">
					<button type="button" class="ui icon button" id="layout-builder-undo" title="<?php echo $this->lang('Undo') ?>" disabled><?php echo icon('fas fa-undo') ?></button>
					<button type="button" class="ui icon button" id="layout-builder-redo" title="<?php echo $this->lang('Redo') ?>" disabled><?php echo icon('fas fa-redo') ?></button>
					<a class="ui button" href="<?php echo url('admin/live-editor?outline_id='.(int)$outline_id.'&legacy=1') ?>"><?php echo icon('fas fa-tools').' '.$this->lang('Legacy editor') ?></a>
					<a class="ui button" href="<?php echo url() ?>" target="_blank" rel="noopener"><?php echo icon('fas fa-eye').' '.$this->lang('Preview') ?></a>
					<button type="button" class="ui primary button" id="layout-builder-save" disabled><?php echo icon('fas fa-save').' '.$this->lang('Save layout') ?></button>
				</div>
			</div>
			<div id="layout-builder-canvas" class="layout-builder-canvas" aria-live="polite"></div>
		</main>
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
