<div class="content">
	<?php echo is_array($fields) ? implode('', $fields) : $fields; ?>
</div>
<?php if (!empty($buttons)): ?>
<div class="right aligned extra content">
	<?php echo implode('', $buttons); ?>
</div>
<?php endif; ?>
