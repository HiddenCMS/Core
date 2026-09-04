<?php $groups = $this->groups(); if (!empty($groups)): ?>
	<form class="ui form" action="<?php echo url($this->url->request) ?>" method="post">
		<ul class="groups user-group-list">
		<?php foreach ($groups as $group_id => $group): ?>
			<?php if ($group['users'] === NULL) continue ?>
			<li>
				<div class="ui checkbox user-group-choice">
					<input type="checkbox" id="group-<?php echo $form_id ?>-<?php echo $group_id ?>" name="<?php echo $form_id ?>[groups][]" value="<?php echo $group_id ?>"<?php if (in_array($user_id, $group['users'])) echo ' checked="checked"'; if ($group['auto'] && $group['auto'] != 'HiddenCMS') echo ' disabled="disabled"' ?> />
					<label for="group-<?php echo $form_id ?>-<?php echo $group_id ?>">
						<?php echo $this->groups->display($group_id, TRUE, FALSE) ?>
					</label>
				</div>
			</li>
		<?php endforeach ?>
		</ul>
		<div class="ui right aligned basic segment">
			<?php echo $this->button_submit($this->lang('Valider')) ?>
		</div>
	</form>
<?php endif ?>
