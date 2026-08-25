<h4 class="ui dividing header"><?php echo icon('fas fa-cogs').' '.$this->lang('Options') ?></h4>
<div class="fields">
	<div class="four wide field">
		<label for="settings-display"><?php echo $this->lang('Affichage') ?></label>
	</div>
	<div class="four wide field">
		<select class="ui search selection dropdown" name="settings[display]" id="settings-display">
			<option value="logo"<?php if (isset($display) && $display == 'logo') echo ' selected="selected"' ?>><?php echo $this->lang('Logo') ?></option>
			<option value="title"<?php if (!isset($display) || $display == 'title') echo ' selected="selected"' ?>><?php echo $this->lang('Titre et slogan') ?></option>
		</select>
	</div>
</div>
<div class="fields">
	<div class="four wide field">
		<label for="settings-align"><?php echo $this->lang('Alignement') ?></label>
	</div>
	<div class="four wide field">
		<select class="ui search selection dropdown" name="settings[align]" id="settings-align">
			<option value="text-left"<?php if (isset($align) && $align == 'text-left') echo ' selected="selected"' ?>><?php echo $this->lang('Gauche') ?></option>
			<option value="text-center"<?php if (!isset($align) || $align == 'text-center') echo ' selected="selected"' ?>><?php echo $this->lang('Centre') ?></option>
			<option value="text-right"<?php if (isset($align) && $align == 'text-right') echo ' selected="selected"' ?>><?php echo $this->lang('Droite') ?></option>
		</select>
	</div>
</div>
<div class="fields">
	<div class="four wide field">
		<label for="settings-title"><?php echo $this->lang('Titre du site') ?></label>
	</div>
	<div class="eight wide field">
		<input type="text" name="settings[title]" value="<?php if (isset($title)) echo $title ?>" id="settings-title" placeholder="<?php echo $this->lang('Titre par defaut') ?>" />
	</div>
	<div class="four wide field">
		<div class="ui left icon input">
			<?php echo icon('fas fa-paint-brush') ?>
			<input type="text" name="settings[color_title]" value="<?php if (isset($color_title)) echo $color_title ?>" placeholder="#000000" />
		</div>
	</div>
</div>
<div class="fields">
	<div class="four wide field">
		<label for="settings-description"><?php echo $this->lang('Description') ?></label>
	</div>
	<div class="eight wide field">
		<input type="text" name="settings[description]" value="<?php if (isset($description)) echo $description ?>" id="settings-description" placeholder="<?php echo $this->lang('Description par defaut') ?>" />
	</div>
	<div class="four wide field">
		<div class="ui left icon input">
			<?php echo icon('fas fa-paint-brush') ?>
			<input type="text" name="settings[color_description]" value="<?php if (isset($color_description)) echo $color_description ?>" placeholder="#000000" />
		</div>
	</div>
</div>
