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
		$status = $this->core_updater->status_snapshot();
		$latest = !empty($status['core']['latest']) ? $status['core']['latest'] : '';

		return $this->modal($this->lang('Update HiddenCMS'), 'fas fa-cloud-download-alt')
					->body('<div class="ui warning message"><div class="header">'.$this->lang('Automatic backup and maintenance').'</div><p>'.$this->lang('HiddenCMS will back up the database and managed files before installing version <strong>%s</strong>. An automatic rollback will be attempted if the update fails.', htmlspecialchars($latest)).'</p></div>')
					->submit($this->lang('Install update'), 'primary')
					->cancel()
					->callback(function(){
						try
						{
							$this->core_updater->update_core();
							notify($this->lang('HiddenCMS was updated successfully.'), 'success');
						}
						catch (\Throwable $e)
						{
							notify($this->lang('Update failed: %s Details remain available on the Updates page.', htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')), 'danger');
						}

						refresh('admin/settings/updates');
					});
	}

	public function addon_update($vendor, $name)
	{
		$package = strtolower($vendor.'/'.$name);
		$status = $this->core_updater->status_snapshot();
		$update = isset($status['addons'][$package]) ? $status['addons'][$package] : NULL;

		if (!$update)
		{
			return $this->modal($this->lang('Update an addon'), 'fas fa-sync')
						->body('<div class="ui message">'.$this->lang('No update is currently available for <code>%s</code>.', htmlspecialchars($package, ENT_QUOTES, 'UTF-8')).'</div>')
						->close();
		}

		return $this->modal($this->lang('Update %s', $package), 'fas fa-sync')
					->body('<div class="ui info message"><div class="header">'.$this->lang('Composer update').'</div><p>'.$this->lang('Package <code>%s</code> will be updated from <strong>%s</strong> to <strong>%s</strong>. Migrations provided by the addon will then be applied.', htmlspecialchars($package, ENT_QUOTES, 'UTF-8'), htmlspecialchars($update['current'], ENT_QUOTES, 'UTF-8'), htmlspecialchars($update['latest'], ENT_QUOTES, 'UTF-8')).'</p></div>')
					->submit($this->lang('Update'), 'primary')
					->cancel()
					->callback(function() use ($package){
						try
						{
							$this->addon_packages->update_package($package);
							$this->core_updater->clear_status_cache();
							$this->addon_packages->sync();
							notify($this->lang('Package <b>%s</b> has been updated.', $package), 'success');
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
		return $this->modal($this->lang('Create a backup'), 'fas fa-archive')
					->body($this->lang('A copy of the database and all core-managed files will be created.'))
					->submit($this->lang('Create backup'), 'primary')
					->cancel()
					->callback(function(){
						try
						{
							$id = $this->core_updater->create_backup();
							notify($this->lang('Backup <b>%s</b> created.', $id), 'success');
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
		return $this->modal($this->lang('Restore a backup'), 'fas fa-history')
					->body('<div class="ui negative message"><div class="header">'.$this->lang('The site will be restored to an earlier state').'</div><p>'.$this->lang('Managed files and the database will be replaced with backup <strong>%s</strong>.', htmlspecialchars($id)).'</p></div>')
					->submit($this->lang('Restore'), 'primary')
					->cancel()
					->callback(function() use ($id){
						try
						{
							$this->core_updater->rollback($id);
							notify($this->lang('Backup <b>%s</b> has been restored.', $id), 'success');
						}
						catch (\Throwable $e)
						{
							notify($e->getMessage(), 'danger');
						}

						refresh('admin/settings/updates');
					});
	}

	public function retention_preview()
	{
		$retention = $this->model('retention');
		$report = $retention->run(FALSE);
		return $this->modal($this->lang('Retention preview'), 'fas fa-search')
			->body('<div class="ui info message"><p>'.$this->lang('No data was changed. These items would be affected by the current settings.').'</p></div>'.$retention->summary($report))
			->close();
	}

	public function retention_purge()
	{
		$retention = $this->model('retention');
		$report = $retention->preview();
		return $this->modal($this->lang('Run retention purge'), 'fas fa-trash-alt')
			->body('<div class="ui negative message"><div class="header">'.$this->lang('This operation will permanently delete expired data').'</div><p>'.$this->lang('Inactive accounts will not be deleted. The three most recent backups will be retained.').'</p></div>'.$retention->summary($report))
			->submit($this->lang('Run purge'), 'danger')
			->cancel()
			->callback(function() use ($retention){
				try
				{
					$report = $retention->run(TRUE);
					notify($this->lang('%d items deleted.', array_sum($report['deleted'])), 'success');
				}
				catch (\Throwable $e)
				{
					notify($e->getMessage(), 'danger');
				}
				refresh('admin/settings/privacy');
			});
	}

	public function maintenance()
	{
		$this->config('maintenance', (string)post('closed') === '1', 'bool');

		return $this->json([
			'status' => (bool)$this->config->maintenance
		]);
	}
}


