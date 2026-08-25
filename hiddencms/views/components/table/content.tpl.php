<?php if ($no_data): ?>
	<div class="clearfix"></div><?php echo $no_data_message ?>
<?php else: ?>
	<?php if ($show_items_per_page): ?>
		<div class="form-group float-left">
			<select class="form-control" style="width: auto;" onchange="window.location='<?php echo url($this->output->module()->pagination->get_url()) ?>/'+$(this).find('option:selected').data('url')" autocomplete="off">
				<?php foreach ($items_per_page as $option): ?>
					<option value="<?php echo $option['value'] ?>"<?php if ($option['selected']): ?> selected="selected"<?php endif ?> data-url="<?php echo $option['url'] ?>"><?php echo $option['label'] ?></option>
				<?php endforeach ?>
			</select>
		</div>
	<?php endif ?>
	<?php echo $pagination_top ?>
	<table class="table table-hover table-striped">
		<?php if ($header_columns): ?>
			<thead>
				<tr class="navbar-inner">
					<?php foreach ($header_columns as $column): ?>
						<?php
						$classes = [];
						if ($column['compact']) $classes[] = 'action';
						if ($column['sortable']) $classes[] = 'sort';
						if ($column['sortable']) $classes[] = $column['sort_state'] === 'asc' ? 'sorting_asc' : ($column['sort_state'] === 'desc' ? 'sorting_desc' : 'sorting');
						if ($column['align']) $classes[] = 'text-'.$column['align'];
						?>
						<th<?php if ($classes): ?> class="<?php echo implode(' ', $classes) ?>"<?php endif ?><?php if ($column['width']): ?> style="width: <?php echo $column['width'] ?>;"<?php endif ?><?php if ($column['sortable']): ?> data-column="<?php echo $column['column'] ?>" data-order-by="<?php echo $column['next_order'] ?>"<?php endif ?>><?php echo $column['title'] ?></th>
					<?php endforeach ?>
				</tr>
			</thead>
		<?php endif ?>
		<tbody>
			<?php foreach ($rows as $row): ?>
				<tr>
					<?php foreach ($row['cells'] as $cell): ?>
						<?php if ($cell['type'] === 'actions'): ?>
							<td class="action"><?php echo implode('&nbsp;', $cell['actions']) ?></td>
						<?php else: ?>
							<?php if ($cell['render_td']): ?>
								<?php
								$classes = [];
								if ($cell['compact']) $classes[] = 'action';
								if ($cell['class']) $classes[] = $cell['class'];
								if ($cell['align']) $classes[] = 'text-'.$cell['align'];
								?>
								<td<?php if ($classes): ?> class="<?php echo implode(' ', $classes) ?>"<?php endif ?>><?php echo $cell['content'] ?></td>
							<?php else: ?>
								<?php echo $cell['content'] ?>
							<?php endif ?>
						<?php endif ?>
					<?php endforeach ?>
				</tr>
			<?php endforeach ?>
		</tbody>
		<?php if ($footer_columns): ?>
			<tfoot>
				<tr class="navbar-inner">
					<?php foreach ($footer_columns as $column): ?>
						<?php
						$classes = [];
						if ($column['compact']) $classes[] = 'action';
						if ($column['sortable']) $classes[] = 'sort';
						if ($column['sortable']) $classes[] = $column['sort_state'] === 'asc' ? 'sorting_asc' : ($column['sort_state'] === 'desc' ? 'sorting_desc' : 'sorting');
						if ($column['align']) $classes[] = 'text-'.$column['align'];
						?>
						<th<?php if ($classes): ?> class="<?php echo implode(' ', $classes) ?>"<?php endif ?><?php if ($column['width']): ?> style="width: <?php echo $column['width'] ?>;"<?php endif ?><?php if ($column['sortable']): ?> data-column="<?php echo $column['column'] ?>" data-order-by="<?php echo $column['next_order'] ?>"<?php endif ?>><?php echo $column['title'] ?></th>
					<?php endforeach ?>
				</tr>
			</tfoot>
		<?php endif ?>
	</table>
	<?php if ($pagination_bottom): ?>
		<div class="float-right"><?php echo $pagination_bottom ?></div>
	<?php endif ?>
	<i><?php echo $results_label ?></i>
<?php endif ?>
