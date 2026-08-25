<form action="<?php echo $action; ?>" method="post"<?php echo $has_upload ? ' enctype="multipart/form-data"' : ''; ?>>
	<fieldset><?php echo $content; ?></fieldset>
</form>
