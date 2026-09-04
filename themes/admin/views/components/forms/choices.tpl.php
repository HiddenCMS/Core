<?php foreach ($items as $item): ?>
	<div class="field<?php if ($inline): ?> inline<?php endif ?>">
		<div class="ui <?php echo !empty($toggle) ? 'toggle' : $type ?> checkbox">
			<?php echo $item['input'] ?>
			<label for="<?php echo $item['id'] ?>"><?php echo $item['label'] ?></label>
		</div>
	</div>
<?php endforeach ?>
