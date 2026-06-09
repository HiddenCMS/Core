<input type="hidden" name="module" value="<?php echo $module ?>" />
<input type="hidden" name="type" value="<?php echo $type ?>" />
<input type="hidden" name="id" value="<?php echo $id ?>" />

<?php foreach ($access as $category): ?>
	<div class="access-category">
		<div class="access-category-title"><?php echo icon($category['icon']).' '.$category['title'] ?></div>
		<div class="access-action-list">
			<?php foreach ($category['access'] as $name => $access): ?>
				<a href="#" class="access-action" data-action="<?php echo $name ?>">
					<span class="access-action-title"><?php echo icon($access['icon']).' '.$access['title'] ?></span>
					<span class="access-action-meta">
						<span class="access-count"><?php echo HB()->access->count($module, $name, $id) ?></span>
						<?php echo icon('fas fa-chevron-right') ?>
					</span>
				</a>
			<?php endforeach ?>
		</div>
	</div>
<?php endforeach ?>
