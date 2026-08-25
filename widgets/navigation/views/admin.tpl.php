<?php $selected_menu_id = isset($menu_id) ? (string)$menu_id : ''; ?>
<h4 class="ui dividing header"><?php echo icon('fas fa-cogs').' '.$this->lang('Options') ?></h4>
<div class="fields">
	<div class="four wide field">
		<label for="settings-menu-id"><?php echo $this->lang('Menu') ?></label>
	</div>
	<div class="eight wide field">
		<select class="ui search selection dropdown" name="settings[menu_id]" id="settings-menu-id">
			<option value=""><?php echo $this->lang('Selectionner un menu') ?></option>
			<?php foreach (isset($menus) ? $menus : [] as $id => $menu_title): ?>
				<option value="<?php echo $id ?>"<?php if ((string)$id === $selected_menu_id) echo ' selected="selected"' ?>><?php echo $menu_title ?></option>
			<?php endforeach ?>
		</select>
		<?php if (empty($menus)): ?>
			<div class="ui tiny message"><?php echo $this->lang('Aucun menu disponible. Creez un menu dans le module Menus.') ?></div>
		<?php endif ?>
	</div>
</div>
<div class="fields">
	<div class="four wide field">
		<label for="settings-panel"><?php echo $this->lang('Panneau') ?></label>
	</div>
	<div class="eight wide field">
		<select class="ui search selection dropdown" name="settings[panel]" id="settings-panel">
			<option value="1"<?php if (!isset($panel) || $panel) echo ' selected="selected"' ?>><?php echo $this->lang('Active') ?></option>
			<option value="0"<?php if (isset($panel) && !$panel) echo ' selected="selected"' ?>><?php echo $this->lang('Desactive') ?></option>
		</select>
	</div>
</div>
