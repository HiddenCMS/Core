<?php $selected_menu_id = isset($menu_id) ? (string)$menu_id : ''; ?>
<ul class="nav nav-pills" id="pills-tab" role="tablist">
	<li class="nav-item"><a class="nav-link active" id="pills-options-tab" data-toggle="pill" href="#pills-options" role="tab" aria-controls="pills-options" aria-selected="true"><?php echo icon('fas fa-cogs').' '.$this->lang('Options') ?></a></li>
</ul>
<div class="tab-content border-light" id="pills-tabContent">
	<div class="tab-pane fade show active" id="pills-options" role="tabpanel" aria-labelledby="pills-options-tab">
		<div class="form-group row">
			<label for="settings-menu-id" class="col-3 col-form-label"><?php echo $this->lang('Menu') ?></label>
			<div class="col-6">
				<select class="form-control" name="settings[menu_id]" id="settings-menu-id">
					<option value=""><?php echo $this->lang('Selectionner un menu') ?></option>
					<?php foreach (isset($menus) ? $menus : [] as $id => $menu_title): ?>
						<option value="<?php echo $id ?>"<?php if ((string)$id === $selected_menu_id) echo ' selected="selected"' ?>><?php echo $menu_title ?></option>
					<?php endforeach ?>
				</select>
				<?php if (empty($menus)): ?>
					<small class="form-text text-muted"><?php echo $this->lang('Aucun menu disponible. Creez un menu dans le module Menus.') ?></small>
				<?php endif ?>
			</div>
		</div>
		<div class="form-group row">
			<label for="settings-panel" class="col-3 col-form-label"><?php echo $this->lang('Panneau') ?></label>
			<div class="col-6">
				<select class="form-control" name="settings[panel]" id="settings-panel">
					<option value="1"<?php if (!isset($panel) || $panel) echo ' selected="selected"' ?>><?php echo $this->lang('Active') ?></option>
					<option value="0"<?php if (isset($panel) && !$panel) echo ' selected="selected"' ?>><?php echo $this->lang('Desactive') ?></option>
				</select>
			</div>
		</div>
	</div>
</div>

