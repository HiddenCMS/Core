<h4 class="ui dividing header"><?php echo icon('fas fa-cogs').' Options' ?></h4>
<div class="fields">
	<div class="four wide field">
		<label>Alignement</label>
	</div>
	<div class="five wide field">
		<div class="inline fields">
			<div class="field">
				<div class="ui radio checkbox">
					<input type="radio" name="settings[align]" value="justify-content-start"<?php if (!isset($align) || $align != 'justify-content-start') echo ' checked="checked"' ?> />
					<label>a gauche</label>
				</div>
			</div>
			<div class="field">
				<div class="ui radio checkbox">
					<input type="radio" name="settings[align]" value="justify-content-end"<?php if (isset($align) && $align == 'justify-content-end') echo ' checked="checked"' ?> />
					<label>a droite</label>
				</div>
			</div>
		</div>
	</div>
</div>
