<ul class="nav nav-pills" id="pills-tab" role="tablist">
	<li class="nav-item"><a class="nav-link active" id="pills-options-tab" data-toggle="pill" href="#pills-options" role="tab" aria-controls="pills-options" aria-selected="true"><?php echo icon('fas fa-cogs').' Options' ?></a></li>
</ul>
<div class="tab-content border-light" id="pills-tabContent">
	<div class="tab-pane fade show active" id="pills-options" role="tabpanel" aria-labelledby="pills-options-tab">
		<div class="fields">
			<label for="settings-display" class="four wide field"><?php echo $this->lang('Affichage') ?></label>
			<div class="four wide field">
				<select class="form-control" name="settings[display]" id="settings-display">
					<option value="logo"<?php if (isset($display) && $display == 'logo') echo ' selected="selected"' ?>><?php echo $this->lang('Logo') ?></option>
					<option value="title"<?php if (!isset($display) || $display == 'title') echo ' selected="selected"' ?>><?php echo $this->lang('Titre et slogan') ?></option>
				</select>
			</div>
		</div>
		<div class="fields">
			<label for="settings-align" class="four wide field"><?php echo $this->lang('Alignement') ?></label>
			<div class="four wide field">
				<select class="form-control" name="settings[align]" id="settings-align">
					<option value="text-left"<?php if (isset($align) && $align == 'text-left') echo ' selected="selected"' ?>><?php echo $this->lang('Gauche') ?></option>
					<option value="text-center"<?php if (!isset($align) || $align == 'text-center') echo ' selected="selected"' ?>><?php echo $this->lang('Centré') ?></option>
					<option value="text-right"<?php if (isset($align) && $align == 'text-right') echo ' selected="selected"' ?>><?php echo $this->lang('Droite') ?></option>
				</select>
			</div>
		</div>
		<div class="fields">
			<label for="settings-title" class="four wide field"><?php echo $this->lang('Titre du site') ?></label>
			<div class="eight wide field">
				<input type="text" class="form-control" name="settings[title]" value="<?php if (isset($title)) echo $title ?>" id="settings-title" placeholder="<?php echo $this->lang('Titre par défaut') ?>" />
			</div>
			<div class="four wide field">
				<div class="input-group">
					<div class="input-group-prepend">
						<span class="input-group-text"><?php echo icon('fas fa-paint-brush') ?></span>
					</div>
					<input type="text" class="form-control" name="settings[color_title]" value="<?php if (isset($color_title)) echo $color_title ?>" placeholder="#000000" /><!-- //TODO color picker -->
				</div>
			</div>
		</div>
		<div class="fields">
			<label for="settings-description" class="four wide field"><?php echo $this->lang('Description') ?></label>
			<div class="eight wide field">
				<input type="text" class="form-control" name="settings[description]" value="<?php if (isset($description)) echo $description ?>" id="settings-description" placeholder="<?php echo $this->lang('Description par défaut') ?>" />
			</div>
			<div class="four wide field">
				<div class="input-group">
					<div class="input-group-prepend">
						<span class="input-group-text"><?php echo icon('fas fa-paint-brush') ?></span>
					</div>
					<input type="text" class="form-control" name="settings[color_description]" value="<?php if (isset($color_description)) echo $color_description ?>" placeholder="#000000" /><!-- //TODO color picker -->
				</div>
			</div>
		</div>
	</div>
</div>
