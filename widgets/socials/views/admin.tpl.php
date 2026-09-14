<ul class="nav nav-pills" id="pills-tab" role="tablist">
	<li class="nav-item"><a class="nav-link active" id="pills-options-tab" data-toggle="pill" href="#pills-options" role="tab" aria-controls="pills-options" aria-selected="true"><?php echo icon('fas fa-cogs').' '.(string)$this->lang('Layout') ?></a></li>
	<li class="nav-item"><a class="nav-link" id="pills-style-tab" data-toggle="pill" href="#pills-style" role="tab" aria-controls="pills-style" aria-selected="false"><?php echo icon('fas fa-paint-brush').' '.(string)$this->lang('Style') ?></a></li>
	<li class="nav-item"><a class="nav-link" id="pills-display-tab" data-toggle="pill" href="#pills-display" role="tab" aria-controls="pills-display" aria-selected="false"><?php echo icon('fas fa-desktop').' '.(string)$this->lang('Display') ?></a></li>
</ul>
<div class="tab-content border-light" id="pills-tabContent">
	<div class="tab-pane fade show active" id="pills-options" role="tabpanel" aria-labelledby="pills-options-tab">
		<div class="fields">
			<label for="settings-display_panel" class="four wide field"><?php echo $this->lang('Show in a panel') ?></label>
			<div class="three wide field">
				<select class="ui search selection dropdown" name="settings[display_panel]" id="settings-display_panel">
					<option value="non"<?php if (!isset($display_panel) || $display_panel == 'non') echo ' selected="selected"' ?>><?php echo $this->lang('No') ?></option>
					<option value="oui"<?php if (isset($display_panel) && $display_panel == 'oui') echo ' selected="selected"' ?>><?php echo $this->lang('Yes') ?></option>
				</select>
			</div>
		</div>
		<div class="fields">
			<label for="settings-social_display" class="four wide field"><?php echo $this->lang('Layout') ?></label>
			<div class="twelve wide field">
				<select class="ui search selection dropdown" name="settings[social_display]" id="settings-display_teamname">
					<option value="col-12"<?php if (!isset($social_display) || $social_display == 'col-12') echo ' selected="selected"' ?>><?php echo $this->lang('1 button per row') ?></option>
					<option value="col-6"<?php if (isset($social_display) && $social_display == 'col-6') echo ' selected="selected"' ?>><?php echo $this->lang('2 buttons per row') ?></option>
					<option value="col-4"<?php if (isset($social_display) && $social_display == 'col-4') echo ' selected="selected"' ?>><?php echo $this->lang('3 buttons per row') ?></option>
					<option value="col-3"<?php if (isset($social_display) && $social_display == 'col-3') echo ' selected="selected"' ?>><?php echo $this->lang('4 buttons per row') ?></option>
					<option value="col-2"<?php if (isset($social_display) && $social_display == 'col-2') echo ' selected="selected"' ?>><?php echo $this->lang('6 buttons per row') ?></option>
					<option value="col-1"<?php if (isset($social_display) && $social_display == 'col-1') echo ' selected="selected"' ?>><?php echo $this->lang('12 buttons per row') ?></option>
					<option value="col"<?php if (isset($social_display) && $social_display == 'col') echo ' selected="selected"' ?>><?php echo $this->lang('Automatic distribution') ?></option>
					<option value="ul-inline"<?php if (isset($social_display) && $social_display == 'ul-inline') echo ' selected="selected"' ?>><?php echo $this->lang('Horizontal list') ?></option>
					<option value="ul"<?php if (isset($social_display) && $social_display == 'ul') echo ' selected="selected"' ?>><?php echo $this->lang('Vertical list') ?></option>
				</select>
			</div>
		</div>
	</div>
	<div class="tab-pane fade" id="pills-style" role="tabpanel" aria-labelledby="pills-style-tab">
		<div class="fields">
			<label for="settings-social_style" class="four wide field"><?php echo $this->lang('Appearance') ?></label>
			<div class="eight wide field">
				<select class="ui search selection dropdown" name="settings[social_style]" id="settings-social_style">
					<option value="btn btn-social"<?php if (!isset($social_style) || $social_style == 'btn btn-social') echo ' selected="selected"' ?>><?php echo $this->lang('Regular button') ?></option>
					<option value="btn btn-social btn-sm"<?php if (isset($social_style) && $social_style == 'btn btn-social btn-sm') echo ' selected="selected"' ?>><?php echo $this->lang('Small button') ?></option>
					<option value="btn btn-social btn-lg"<?php if (isset($social_style) && $social_style == 'btn btn-social btn-lg') echo ' selected="selected"' ?>><?php echo $this->lang('Large button') ?></option>
					<option value="btn btn-link"<?php if (isset($social_style) && $social_style == 'btn btn-link') echo ' selected="selected"' ?>><?php echo $this->lang('Plain link') ?></option>
				</select>
			</div>
		</div>
		<div class="fields">
			<label for="settings-content_display" class="four wide field"><?php echo $this->lang('Content') ?></label>
			<div class="eight wide field">
				<select class="ui search selection dropdown" name="settings[content_display]" id="settings-content_display">
					<option value="all"<?php if (!isset($content_display) || $content_display == 'all') echo ' selected="selected"' ?>><?php echo $this->lang('Icon and caption') ?></option>
					<option value="icon"<?php if (isset($content_display) && $content_display == 'icon') echo ' selected="selected"' ?>><?php echo $this->lang('Icon only') ?></option>
					<option value="legend"<?php if (isset($content_display) && $content_display == 'legend') echo ' selected="selected"' ?>><?php echo $this->lang('Caption only') ?></option>
				</select>
			</div>
		</div>
		<div class="fields">
			<label for="settings-icon_size" class="four wide field"><?php echo $this->lang('Icon size') ?></label>
			<div class="four wide field">
				<select class="ui search selection dropdown" name="settings[icon_size]" id="settings-icon_size">
					<option value="fa-1x"<?php if (!isset($icon_size) || $icon_size == 'fa-1x') echo ' selected="selected"' ?>><?php echo $this->lang('Default') ?></option>
					<option value="fa-2x"<?php if (isset($icon_size) && $icon_size == 'fa-2x') echo ' selected="selected"' ?>><?php echo $this->lang('Large') ?></option>
					<option value="fa-3x"<?php if (isset($icon_size) && $icon_size == 'fa-3x') echo ' selected="selected"' ?>><?php echo $this->lang('Very large') ?></option>
					<option value="fa-4x"<?php if (isset($icon_size) && $icon_size == 'fa-4x') echo ' selected="selected"' ?>><?php echo $this->lang('Huge') ?></option>
				</select>
			</div>
		</div>
	</div>
	<div class="tab-pane fade" id="pills-display" role="tabpanel" aria-labelledby="pills-display-tab">
		<div class="fields">
			<label for="settings-margin_top" class="four wide field"><?php echo $this->lang('Top margin') ?></label>
			<div class="five wide field">
				<div class="fields mb-0">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><?php echo icon('fas fa-caret-up') ?></div>
						</div>
						<input type="number" name="settings[margin_top]" value="<?php echo $margin_top ? $margin_top : '0' ?>">
						<div class="input-group-append">
							<div class="input-group-text">px</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="fields">
			<label for="settings-margin_right" class="four wide field"><?php echo $this->lang('Right margin') ?></label>
			<div class="five wide field">
				<div class="fields mb-0">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><?php echo icon('fas fa-caret-right') ?></div>
						</div>
						<input type="number" name="settings[margin_right]" value="<?php echo $margin_right ? $margin_right : '0' ?>">
						<div class="input-group-append">
							<div class="input-group-text">px</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="fields">
			<label for="settings-margin_bottom" class="four wide field"><?php echo $this->lang('Bottom margin') ?></label>
			<div class="five wide field">
				<div class="fields mb-0">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><?php echo icon('fas fa-caret-down') ?></div>
						</div>
						<input type="number" name="settings[margin_bottom]" value="<?php echo $margin_bottom ? $margin_bottom : '0' ?>">
						<div class="input-group-append">
							<div class="input-group-text">px</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="fields">
			<label for="settings-margin_left" class="four wide field"><?php echo $this->lang('Left margin') ?></label>
			<div class="five wide field">
				<div class="fields mb-0">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><?php echo icon('fas fa-caret-left') ?></div>
						</div>
						<input type="number" name="settings[margin_left]" value="<?php echo $margin_left ? $margin_left : '0' ?>">
						<div class="input-group-append">
							<div class="input-group-text">px</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="fields">
			<label for="settings-padding_top" class="four wide field"><?php echo $this->lang('Top padding') ?></label>
			<div class="five wide field">
				<div class="fields mb-0">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><?php echo icon('far fa-caret-square-up') ?></div>
						</div>
						<input type="number" name="settings[padding_top]" value="<?php echo $padding_top ? $padding_top : '0' ?>">
						<div class="input-group-append">
							<div class="input-group-text">px</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="fields">
			<label for="settings-padding_right" class="four wide field"><?php echo $this->lang('Right padding') ?></label>
			<div class="five wide field">
				<div class="fields mb-0">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><?php echo icon('far fa-caret-square-right') ?></div>
						</div>
						<input type="number" name="settings[padding_right]" value="<?php echo $padding_right ? $padding_right : '0' ?>">
						<div class="input-group-append">
							<div class="input-group-text">px</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="fields">
			<label for="settings-padding_bottom" class="four wide field"><?php echo $this->lang('Bottom padding') ?></label>
			<div class="five wide field">
				<div class="fields mb-0">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><?php echo icon('far fa-caret-square-down') ?></div>
						</div>
						<input type="number" name="settings[padding_bottom]" value="<?php echo $padding_bottom ? $padding_bottom : '0' ?>">
						<div class="input-group-append">
							<div class="input-group-text">px</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="fields">
			<label for="settings-padding_left" class="four wide field"><?php echo $this->lang('Left padding') ?></label>
			<div class="five wide field">
				<div class="fields mb-0">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text"><?php echo icon('far fa-caret-square-left') ?></div>
						</div>
						<input type="number" name="settings[padding_left]" value="<?php echo $padding_left ? $padding_left : '0' ?>">
						<div class="input-group-append">
							<div class="input-group-text">px</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
