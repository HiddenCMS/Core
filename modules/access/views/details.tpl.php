<table class="table table-access table-hover">
	<thead>
		<tr>
			<th class="ten wide"><?php echo $this->lang('Groupes') ?></th>
			<th class="three wide center aligned" data-radio="success">
				<div data-toggle="tooltip" title="<?php echo $this->lang('Groupe autoris?') ?>"><?php echo icon('fas fa-check') ?></div>
			</th>
			<th class="three wide center aligned" data-radio="danger">
				<div data-toggle="tooltip" title="<?php echo $this->lang('Groupe exclu') ?>"><?php echo icon('fas fa-ban') ?></div>
			</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($groups as $group_id => $active): ?>
		<tr data-group="<?php echo $group_id ?>">
			<td><?php echo $this->groups->display($group_id) ?></td>
			<?php echo $this->view('radio', ['class' => 'success', 'active' => $active]) ?>
			<?php echo $group_id == 'admins' ? '<td></td>' : $this->view('radio', ['class' => 'danger', 'active' => !$active]) ?>
		</tr>
		<?php endforeach ?>
	</tbody>
</table>
