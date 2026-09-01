<div class="addons-toolbar">
	<div class="addons-filter-group">
		<span class="addons-filter-label"><?php echo icon('fas fa-filter') ?> <?php echo $this->lang('Type') ?></span>
		<div class="ui compact secondary menu addons-filter" data-filter-group="type">
			<button type="button" class="active item" data-filter="all"><?php echo $this->lang('Tous') ?></button>
			<button type="button" class="item" data-filter=".addon-module"><?php echo $this->lang('Modules') ?></button>
			<button type="button" class="item" data-filter=".addon-theme"><?php echo $this->lang('Thèmes') ?></button>
			<button type="button" class="item" data-filter=".addon-widget"><?php echo $this->lang('Widgets') ?></button>
			<button type="button" class="item" data-filter=".addon-language"><?php echo $this->lang('Langues') ?></button>
			<button type="button" class="item" data-filter=".addon-authenticator"><?php echo $this->lang('Authentificateurs') ?></button>
		</div>
	</div>
	<div class="addons-filter-group addons-filter-status">
		<span class="addons-filter-label"><?php echo $this->lang('État') ?></span>
		<div class="ui compact secondary menu addons-filter" data-filter-group="status">
			<button type="button" class="active item" data-filter="all"><?php echo $this->lang('Tous') ?></button>
			<button type="button" class="item" data-filter=".activated"><?php echo $this->lang('Actifs') ?></button>
			<button type="button" class="item" data-filter=".deactivated"><?php echo $this->lang('Inactifs') ?></button>
		</div>
	</div>
</div>
<div id="addons" class="addons-grid">
	<?php foreach ($addons as $addon): ?>
		<?php $enabled = $addon->addon()->is_enabled(); ?>
		<div class="mix addon-card-wrapper <?php echo ($addon->type ? 'addon-'.$addon->type->name : 'addon').' '.($enabled ? 'activated' : 'deactivated') ?>">
			<article class="ui fluid card addon-card<?php echo $enabled ? '' : ' is-disabled' ?>">
				<div class="content addon-card-content">
					<div class="addon-card-topline">
						<?php $label = $addon->controller()->__label ?>
						<span class="ui tiny label addon-type-label"><?php echo $label[1] ?></span>
						<div class="ui dropdown addon-actions">
							<button type="button" class="addon-actions-trigger" aria-label="<?php echo $this->lang('Actions') ?>"><?php echo icon('fas fa-cog') ?></button>
							<div class="menu">
								<?php foreach ($addon->addon()->__actions as $name => $action): ?>
									<?php if (list($title, $icon, $color, $modal) = $action): ?>
										<?php $url = url('admin/addons/'.$name.'/'.$addon->url()) ?>
										<a class="item addon-action addon-action-<?php echo $color ?>" <?php echo $modal ? 'href="#" data-modal-ajax="'.$url.'"' : 'href="'.$url.'"' ?>><?php echo icon($icon).' '.$title ?></a>
									<?php else: ?>
										<div class="divider"></div>
									<?php endif ?>
								<?php endforeach ?>
							</div>
						</div>
					</div>
					<div class="addon-visual">
						<?php if ($path = $addon->addon()->__path('', 'thumbnail.png')): ?>
							<div class="addon-thumbnail" style="background-image: url(<?php echo url($path) ?>);"></div>
						<?php else: ?>
							<div class="addon-icon"><?php echo icon(isset($addon->addon()->info()->icon) ? $addon->addon()->info()->icon : $label[2]) ?></div>
						<?php endif ?>
					</div>
					<div class="addon-card-copy">
						<h3><?php echo $addon->addon()->info()->title ?></h3>
						<span class="addon-state <?php echo $enabled ? 'enabled' : 'disabled' ?>"><?php echo $enabled ? $this->lang('Actif') : $this->lang('Inactif') ?></span>
					</div>
				</div>
			</article>
		</div>
	<?php endforeach ?>
</div>
