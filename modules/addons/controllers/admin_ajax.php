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
								->title((string)$this->lang('Composer package'))
								->placeholder('vendor/package')
								->info((string)$this->lang('Enter the Composer name of the module, theme, widget or other HiddenCMS-compatible addon.'))
								->required()
								->check(function($post){
									$package = isset($post['package']) ? trim($post['package']) : '';

									if (!preg_match('#^[a-z0-9](?:[a-z0-9_.-]*[a-z0-9])?/[a-z0-9](?:[a-z0-9_.-]*[a-z0-9])?(?::[^\s]+)?$#i', $package))
									{
										return (string)$this->lang('The Composer package name is invalid.');
									}
								})
					)
					->success(function($data, $form){
						try
						{
							$package = trim($data['package']);
							$this->addon_packages->require_package($package);
							$this->addon_packages->sync();

							notify($this->lang('Package %s has been installed. You can now enable its addon.', utf8_htmlentities($package)), 'success');
							refresh();
						}
						catch (Throwable $e)
						{
							$form->error($e->getMessage());
							notify($e->getMessage(), 'danger');
						}
					})
					->submit((string)$this->lang('Install'))
					->modal((string)$this->lang('Install an addon'), 'fas fa-puzzle-piece')
					->cancel();
	}
}
