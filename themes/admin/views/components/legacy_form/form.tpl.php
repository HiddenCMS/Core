<form action="<?php echo $action; ?>" method="post" class="ui form"<?php echo $has_upload ? ' enctype="multipart/form-data"' : ''; ?>>
	<?php echo $content; ?>
</form>
