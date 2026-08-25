<div class="table-area" data-table-id="<?php echo $id ?>"<?php if ($ajax_url): ?> data-ajax-url="<?php echo $ajax_url ?>" data-ajax-post="<?php echo $ajax_post ?>"<?php endif; ?>>
	<?php if ($search_enabled): ?>
		<div class="table-search ui mini icon input">
			<input data-provide="typeahead" data-items="5" data-source="<?php echo $search_source_json ?>" type="text"<?php if ($search_value !== ''): ?> value="<?php echo utf8_htmlentities($search_value) ?>"<?php endif; ?> placeholder="<?php echo $this->lang('Rechercher') ?>" autocomplete="off">
			<i class="search icon"></i>
		</div>
	<?php endif ?>
	<div class="table-content"><?php echo $content ?></div>
</div>
