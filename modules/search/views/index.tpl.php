<h3><?php echo $this->lang('Search') ?></h3>
<?php if ($keywords !== '' && !$results): ?>
<p class="text-info"><?php echo $this->lang('No results found for <b>%s</b>', utf8_htmlentities($keywords)) ?></b></p>
<?php endif ?>
<form action="<?php echo url('search') ?>" method="get">
	<div class="input-group">
		<input type="text" class="form-control form-control-lg" name="q" value="<?php echo utf8_htmlentities($keywords) ?>" placeholder="<?php echo $this->lang('Search for one or more terms on our site') ?>" />
		<span class="input-group-append">
			<button class="btn btn-primary btn-lg" type="submit"><?php echo icon('fas fa-search').' '.$this->lang('Search') ?></button>
		</span>
	</div>
</form>
