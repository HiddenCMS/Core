<?php if ($single_group): ?>
	<?php echo $groups ? $groups[0]['content'] : '' ?>
<?php else: ?>
	<?php foreach ($groups as $group): ?>
		<div class="<?php echo $group['align'] ?> floated"><?php echo $group['content'] ?></div>
	<?php endforeach ?>
<?php endif ?>
