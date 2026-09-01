<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Settings\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Admin_Ajax extends Controller_Module
{
	public function core_update()
	{
		$status = $this->core_updater->status();
		$latest = !empty($status['core']['latest']) ? $status['core']['latest'] : '';

		return $this->modal('Mettre à jour HiddenCMS', 'fas fa-cloud-download-alt')
					->body('<div class="ui warning message"><div class="header">Sauvegarde et maintenance automatiques</div><p>HiddenCMS sauvegardera la base de données et les fichiers gérés avant d\'installer la version <strong>'.htmlspecialchars($latest).'</strong>. Un retour arrière sera tenté automatiquement en cas d\'échec.</p></div>')
					->submit('Installer la mise à jour', 'primary')
					->cancel()
					->callback(function(){
						try
						{
							$this->core_updater->update_core();
							notify('HiddenCMS a été mis à jour avec succès.', 'success');
						}
						catch (\Throwable $e)
						{
							notify($e->getMessage(), 'danger');
						}

						refresh('admin/settings/updates');
					});
	}

	public function backup()
	{
		return $this->modal('Créer une sauvegarde', 'fas fa-archive')
					->body('Une copie de la base de données et de tous les fichiers gérés par le core va être créée.')
					->submit('Créer la sauvegarde', 'primary')
					->cancel()
					->callback(function(){
						try
						{
							$id = $this->core_updater->create_backup();
							notify('Sauvegarde <b>'.$id.'</b> créée.', 'success');
						}
						catch (\Throwable $e)
						{
							notify($e->getMessage(), 'danger');
						}

						refresh('admin/settings/updates');
					});
	}

	public function rollback($id)
	{
		return $this->modal('Restaurer une sauvegarde', 'fas fa-history')
					->body('<div class="ui negative message"><div class="header">Le site sera restauré à un état antérieur</div><p>Les fichiers gérés et la base de données seront remplacés par la sauvegarde <strong>'.htmlspecialchars($id).'</strong>.</p></div>')
					->submit('Restaurer', 'primary')
					->cancel()
					->callback(function() use ($id){
						try
						{
							$this->core_updater->rollback($id);
							notify('La sauvegarde <b>'.$id.'</b> a été restaurée.', 'success');
						}
						catch (\Throwable $e)
						{
							notify($e->getMessage(), 'danger');
						}

						refresh('admin/settings/updates');
					});
	}

	public function maintenance()
	{
		$this->config('maintenance', (bool)post('closed'), 'bool');

		return $this->json([
			'status' => $this->config->maintenance
		]);
	}
}


