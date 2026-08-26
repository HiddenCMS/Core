<?php
$iframe_url = url($this->config->default_page);
$default_mode = \HB\HiddenCMS\Core\Output::ZONES + \HB\HiddenCMS\Core\Output::ROWS + \HB\HiddenCMS\Core\Output::COLS + \HB\HiddenCMS\Core\Output::WIDGETS;
$live_editor = $this->session('live_editor');

if (!$live_editor || !($live_editor & (\HB\HiddenCMS\Core\Output::ZONES + \HB\HiddenCMS\Core\Output::ROWS + \HB\HiddenCMS\Core\Output::COLS)))
{
	$live_editor = $default_mode;
}

if ($outline_id !== NULL)
{
	$iframe_url .= (strpos($iframe_url, '?') === FALSE ? '?' : '&').'outline_id='.$outline_id;
}
?>
<form target="live-editor-iframe" action="<?php echo $iframe_url ?>" method="post">
	<input type="hidden" name="live_editor" value="<?php echo $live_editor ?>" />
	<nav class="ui borderless menu live-editor-navbar">
		<a class="header item live-editor-brand" href="<?php echo url('admin/live-editor') ?>"><?php echo icon('fas fa-desktop') ?> <b>Live</b><span data-typer="Editor"></span></a>
		<?php if (!empty($outlines)): ?>
			<div class="ui dropdown item live-editor-outline-picker" role="button" tabindex="0">
				<?php echo icon('fas fa-layer-group').' '.$this->lang('Outlines') ?>
				<i class="dropdown icon"></i>
				<div class="menu">
					<?php foreach ($outlines as $id => $title): ?>
						<a class="item<?php echo $outline_id == $id ? ' active' : '' ?>" href="<?php echo url('admin/live-editor?outline_id='.$id) ?>"><?php echo $title ?></a>
					<?php endforeach ?>
				</div>
			</div>
		<?php endif ?>
		<div class="item live-editor-map-item">
			<span id="live-editor-map" data-outline-mode="1"<?php echo $outline_title !== '' ? ' data-outline-title="'.utf8_htmlentities(icon('fas fa-layer-group').' '.$outline_title).'"' : '' ?>><?php echo $outline_title !== '' ? icon('fas fa-layer-group').' '.$outline_title : icon('fas fa-spinner fa-spin').' '.$this->lang('Chargement en cours...') ?></span>
		</div>
		<div class="right menu">
			<div class="item">
				<div class="ui tiny compact buttons live-editor-mode-group">
					<button type="button" class="ui button live-editor-mode<?php echo $live_editor & \HB\HiddenCMS\Core\Output::ZONES ? ' active' : '' ?>" data-mode="<?php echo \HB\HiddenCMS\Core\Output::ZONES ?>"><?php echo icon('far fa-square').' '.$this->lang('Zones') ?></button>
					<button type="button" class="ui button live-editor-mode<?php echo $live_editor & \HB\HiddenCMS\Core\Output::ROWS ? ' active' : '' ?>" data-mode="<?php echo \HB\HiddenCMS\Core\Output::ROWS ?>"><?php echo icon('fas fa-columns fa-rotate-270').' '.$this->lang('Lignes') ?></button>
					<button type="button" class="ui button live-editor-mode<?php echo $live_editor & \HB\HiddenCMS\Core\Output::COLS ? ' active' : '' ?>" data-mode="<?php echo \HB\HiddenCMS\Core\Output::COLS ?>"><?php echo icon('fas fa-columns').' '.$this->lang('Colonnes') ?></button>
					<button type="button" class="ui button live-editor-mode<?php echo $live_editor & \HB\HiddenCMS\Core\Output::WIDGETS ? ' active' : '' ?>" data-mode="<?php echo \HB\HiddenCMS\Core\Output::WIDGETS ?>"><?php echo icon('fas fa-th-large').' '.$this->lang('Widgets') ?></button>
				</div>
			</div>
			<div class="ui dropdown icon item live-editor-screen-picker" role="button" tabindex="0" aria-label="<?php echo $this->lang('Taille de l\'ecran') ?>">
				<span class="live-editor-screen-current"><?php echo icon('fas fa-desktop') ?></span>
				<i class="dropdown icon"></i>
				<div class="menu screen">
					<div class="item"><button type="button" class="ui icon button live-editor-screen active" data-width="100%" data-tooltip="<?php echo $this->lang('Ordinateur') ?>"><?php echo icon('fas fa-desktop') ?></button></div>
					<div class="item"><button type="button" class="ui icon button live-editor-screen" data-width="992px" data-tooltip="<?php echo $this->lang('Tablette paysage') ?>"><?php echo icon('fas fa-tablet-alt fa-rotate-270') ?></button></div>
					<div class="item"><button type="button" class="ui icon button live-editor-screen" data-width="768px" data-tooltip="<?php echo $this->lang('Tablette portrait') ?>"><?php echo icon('fas fa-tablet-alt') ?></button></div>
					<div class="item"><button type="button" class="ui icon button live-editor-screen" data-width="400px" data-tooltip="<?php echo $this->lang('Smartphone') ?>"><?php echo icon('fas fa-mobile-alt') ?></button></div>
				</div>
			</div>
			<a href="<?php echo url('admin') ?>" class="item"><?php echo $this->label('Tableau de bord', 'fas fa-tachometer-alt') ?></a>
			<a href="<?php echo url() ?>" class="item live-editor-exit"><?php echo $this->label('Quitter', 'fas fa-power-off') ?></a>
		</div>
	</nav>
</form>
<?php echo icon('far fa-save live-editor-save') ?>
<div class="live-editor-styles-row">
	<?php echo $styles_row ?>
</div>
<div class="live-editor-styles-widget">
	<?php echo $styles_widget ?>
</div>
<div class="live-editor-iframe">
	<iframe name="live-editor-iframe" src=""></iframe>
</div>


