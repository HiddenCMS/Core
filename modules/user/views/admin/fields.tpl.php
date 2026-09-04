<?php if (!$fields): ?>
	<p>Aucun champ personnalisé.</p>
<?php else: ?>
	<table class="ui very basic table">
		<thead><tr><th>Champ</th><th>Type</th><th></th></tr></thead>
		<tbody>
		<?php foreach ($fields as $field): ?>
			<tr>
				<td><?php echo utf8_htmlentities($field['label']) ?><br><span class="ui tiny label"><code><?php echo $field['name'] ?></code></span></td>
				<td><?php echo $types[$field['type']] ?><?php if ($identifier === 'field:'.$field['id']): ?> <i class="fas fa-key" title="Identifiant de connexion"></i><?php endif ?></td>
				<td class="right aligned collapsing">
					<?php echo $this->button_update('admin/user/fields/'.$field['id']) ?>
					<?php if ($identifier !== 'field:'.$field['id']) echo $this->button()->url('admin/user/fields/delete/'.$field['id'])->icon('fas fa-trash')->tooltip('Supprimer')->color('danger')->compact() ?>
				</td>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>
<?php endif ?>
