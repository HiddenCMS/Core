<?php
$core = $status['core'];
$available = !empty($core['available']);
$escape = function($value){
	return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
};
?>

<div class="updates-page">
<div class="updates-overview">
	<div>
		<span class="updates-eyebrow"><?php echo $this->lang('Version installée') ?></span>
		<strong class="updates-version"><?php echo $escape($core['current']) ?></strong>
		<span class="ui tiny label <?php echo $available ? 'updates-label-warning' : 'updates-label-success' ?>">
			<?php echo $available ? $this->lang('Mise à jour disponible') : $this->lang('À jour') ?>
		</span>
	</div>
	<div class="updates-overview-actions">
		<a href="#" class="ui button" data-modal-ajax="<?php echo url('admin/ajax/settings/backup') ?>"><?php echo icon('fas fa-archive').' '.$this->lang('Sauvegarder') ?></a>
		<?php if ($available): ?>
			<a href="#" class="ui primary button" data-modal-ajax="<?php echo url('admin/ajax/settings/core-update') ?>"><?php echo icon('fas fa-cloud-download-alt').' '.$this->lang('Mettre à jour vers %s', $escape($core['latest'])) ?></a>
		<?php endif ?>
	</div>
</div>

<?php if (!empty($core['error'])): ?>
	<div class="ui warning message"><?php echo icon('fas fa-exclamation-triangle') ?> <?php echo $escape($core['error']) ?></div>
<?php elseif ($available && !empty($core['release'])): ?>
	<div class="updates-release">
		<div>
			<span class="updates-eyebrow"><?php echo $this->lang('Dernière release') ?></span>
			<strong><?php echo $escape($core['release']['name'] ?: $core['latest']) ?></strong>
		</div>
		<?php if (!empty($core['release']['url'])): ?>
			<a href="<?php echo $escape($core['release']['url']) ?>" target="_blank" rel="noopener" class="ui button"><?php echo icon('fas fa-external-link-alt').' '.$this->lang('Notes de version') ?></a>
		<?php endif ?>
	</div>
<?php endif ?>

<?php if ($last_failure): ?>
	<div class="ui negative message updates-failure">
		<div class="header"><?php echo icon('fas fa-exclamation-triangle').' '.$this->lang('La dernière mise à jour a échoué') ?></div>
		<p>
			<?php if (!empty($last_failure['target_version'])): ?><?php echo $this->lang('Version ciblée') ?> : <strong><?php echo $escape($last_failure['target_version']) ?></strong><br /><?php endif ?>
			<?php echo $this->lang('Étape') ?> : <strong><?php echo $escape($last_failure['failure']['stage'] ?? 'inconnue') ?></strong><br />
			<?php echo $escape($last_failure['failure']['message'] ?? 'Erreur non renseignée.') ?>
		</p>
		<?php if (!empty($last_failure['backup']) && empty($last_failure['rollback_error'])): ?>
			<p><?php echo $this->lang('Le site a été restauré automatiquement dans son état précédent.') ?></p>
		<?php elseif (!empty($last_failure['rollback_error'])): ?>
			<p><strong><?php echo $this->lang('Échec du retour arrière') ?> :</strong> <?php echo $escape($last_failure['rollback_error']) ?></p>
		<?php endif ?>
		<p><code>logs/core-updater.log</code></p>
	</div>
<?php endif ?>

<section class="updates-section">
	<header class="updates-section-header">
		<div>
			<h3><?php echo icon('fas fa-puzzle-piece').' '.$this->lang('Paquets HiddenCMS') ?></h3>
			<p><?php echo $this->lang('Mises à jour des paquets officiels et compatibilité déclarée avec le core.') ?></p>
		</div>
	</header>

	<?php if (!empty($status['addons'])): ?>
		<div class="ui very basic table updates-table">
			<table class="ui very basic table">
				<thead><tr><th><?php echo $this->lang('Paquet') ?></th><th><?php echo $this->lang('Installée') ?></th><th><?php echo $this->lang('Disponible') ?></th><th></th></tr></thead>
				<tbody>
				<?php foreach ($status['addons'] as $package): ?>
					<tr>
						<td><code><?php echo $escape($package['package']) ?></code></td>
						<td><?php echo $escape($package['current']) ?></td>
						<td><span class="ui tiny label updates-label-warning"><?php echo $escape($package['latest']) ?></span></td>
						<td class="right aligned"><a href="#" class="ui mini primary button" data-modal-ajax="<?php echo url('admin/ajax/settings/addon-update/'.$package['package']) ?>"><?php echo icon('fas fa-sync').' '.$this->lang('Mettre à jour') ?></a></td>
					</tr>
				<?php endforeach ?>
				</tbody>
			</table>
		</div>
	<?php else: ?>
		<div class="updates-empty"><?php echo icon('fas fa-check-circle').' '.$this->lang('Aucune mise à jour HiddenCMS détectée.') ?></div>
	<?php endif ?>

	<?php if (!empty($status['compatibility'])): ?>
		<div class="updates-compatibility">
			<?php foreach ($status['compatibility'] as $package => $compatibility): ?>
				<div>
					<code><?php echo $escape($package) ?></code>
					<span><?php echo $escape($compatibility['constraint'] ?: 'non déclarée') ?></span>
					<i class="<?php echo $compatibility['compatible'] ? 'fas fa-check-circle' : 'fas fa-exclamation-circle' ?>"></i>
				</div>
			<?php endforeach ?>
		</div>
	<?php endif ?>
</section>

<section class="updates-section">
	<header class="updates-section-header">
		<div>
			<h3><?php echo icon('fas fa-history').' '.$this->lang('Sauvegardes de mise à jour') ?></h3>
			<p><?php echo $this->lang('Chaque mise à jour du core crée automatiquement un point de retour.') ?></p>
		</div>
	</header>

	<?php if ($backups): ?>
		<div class="updates-backups">
			<?php foreach ($backups as $backup): ?>
				<div class="updates-backup-entry">
					<div class="updates-backup-row">
						<div><strong><?php echo $escape($backup['id']) ?></strong><span><?php echo $escape($backup['core_version'].' · '.date('d/m/Y H:i', strtotime($backup['created_at']))) ?></span></div>
						<span class="ui tiny label"><?php echo $escape(['ready' => 'Disponible', 'updating' => 'Mise à jour en cours', 'completed' => 'Mise à jour terminée', 'restored' => 'Restaurée', 'automatic-rollback' => 'Retour automatique', 'rollback-failed' => 'Retour arrière en échec'][$backup['status']] ?? $backup['status']) ?></span>
						<a href="#" class="ui mini button" data-modal-ajax="<?php echo url('admin/ajax/settings/rollback/'.$backup['id']) ?>"><?php echo icon('fas fa-history').' '.$this->lang('Restaurer') ?></a>
					</div>
					<?php if (!empty($backup['failure'])): ?>
						<div class="updates-backup-error"><?php echo icon('fas fa-exclamation-circle') ?> <strong><?php echo $escape($backup['failure']['stage'] ?? 'Erreur') ?> :</strong> <?php echo $escape($backup['failure']['message'] ?? '') ?></div>
					<?php endif ?>
				</div>
			<?php endforeach ?>
		</div>
	<?php else: ?>
		<div class="updates-empty"><?php echo $this->lang('Aucune sauvegarde de mise à jour pour le moment.') ?></div>
	<?php endif ?>
</section>
</div>
