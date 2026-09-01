<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Addons\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;
use Throwable;

class Admin_Ajax extends Controller_Module
{
	public function install()
	{
		return $this->form2()
					->rule($this->form_text('package')
								->title('Paquet Composer')
								->placeholder('vendor/package')
								->info('Indiquez le nom Composer du module, thème, widget ou autre addon compatible HiddenCMS.')
								->required()
								->check(function($post){
									$package = isset($post['package']) ? trim($post['package']) : '';

									if (!preg_match('#^[a-z0-9](?:[a-z0-9_.-]*[a-z0-9])?/[a-z0-9](?:[a-z0-9_.-]*[a-z0-9])?(?::[^\s]+)?$#i', $package))
									{
										return 'Le nom du paquet Composer est invalide.';
									}
								})
					)
					->success(function($data, $form){
						try
						{
							$package = trim($data['package']);
							$this->addon_packages->require_package($package);
							$this->addon_packages->sync();

							notify('Le paquet '.$package.' a été installé. Vous pouvez maintenant activer son addon.', 'success');
							$this->modal->dispose();
							refresh();
						}
						catch (Throwable $e)
						{
							$form->error($e->getMessage());
							notify($e->getMessage(), 'danger');
						}
					})
					->submit('Installer')
					->modal('Installer un addon', 'fas fa-puzzle-piece')
					->cancel();
	}
}
