<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Addons\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Admin extends Controller_Module
{
	public function index()
	{
		$addons = array_filter(HB()->collection('addon')->get(), function($addon){
			$object = $addon->addon();

			if ($object && ($controller = $addon->controller()))
			{
				$actions = $object->__actions = $controller->__actions()->filter(function($action) use ($object){
					return !isset($action[4]) || $action[4]($object);
				});

				if ($object->composer_package())
				{
					$actions->set('package-update', ['Mettre à jour', 'fas fa-sync', 'info', TRUE]);
					$actions->set('package-remove', ['Supprimer', 'fas fa-trash-alt', 'danger', TRUE]);
				}

				return !$actions->empty();
			}
		});

		$types = array_count_values(array_map(function($a){
			return $a->type->id;
		}, $addons));

		usort($addons, function($a, $b) use ($types){
			if ($types[$a->type->id] > $types[$b->type->id])
			{
				return 1;
			}
			else if ($types[$a->type->id] < $types[$b->type->id])
			{
				return -1;
			}
			else
			{
				return str_nat($a, $b, function($a){
					return $a->type->name.$a->addon()->info()->title;
				});
			}
		});

		$this->add_action($this->button('Installer', 'fas fa-download', 'primary')->modal_ajax('admin/ajax/addons/install'));

		return $this->module('settings')->controller('admin')->_layout(function($col) use ($addons){
			$col->append($this	->js('addons')
								->css('addons')
								->view('admin', [
									'addons' => $addons
								])
			);
		});
	}

	public function help($controller, $method)
	{
		return $this->modal('Aide', 'far fa-life-ring')
					->large()
					->body(call_user_func([$controller, $method]))
					->close();
	}

	public function _action($addon, $controller, $action)
	{
		if (in_array($action, ['package-update', 'package-remove'], TRUE))
		{
			$method = '_'.str_replace('-', '_', $action);
			return $this->$method($addon);
		}

		return $controller->$action($addon, $this);
	}

	protected function _package_update($addon)
	{
		$package = $addon->composer_package();

		return $this->modal('Mettre à jour '.$addon->info()->title, 'fas fa-sync')
					->body($this->lang('Composer recherchera et installera la dernière version compatible du paquet <code>%s</code>.', $package))
					->submit('Mettre à jour', 'info')
					->cancel()
					->callback(function() use ($package){
						try
						{
							$this->addon_packages->update_package($package);
							$this->addon_packages->sync();
							notify($this->lang('Le paquet <b>%s</b> a été mis à jour.', $package));
						}
						catch (\Throwable $e)
						{
							notify($e->getMessage(), 'danger');
						}

						refresh();
					});
	}

	protected function _package_remove($addon)
	{
		$package = $addon->composer_package();
		$purge = $this->form_checkbox('purge')
					  ->data([
						  '1' => 'Supprimer également les tables et les données de cet addon'
					  ]);
		$form = $this->form2()
					 ->rule($purge)
					 ->success(function($data) use ($package){
						try
						{
							$purge = !empty($data['purge']) && in_array('1', $data['purge']);
							$this->addon_packages->remove_package($package, $purge);
							notify($this->lang('Le paquet <b>%s</b> a été supprimé.', $package));
							refresh();
						}
						catch (\Throwable $e)
						{
							notify($e->getMessage(), 'danger');
						}
					 });

		$purge->form2($form);

		return $this->modal('Supprimer '.$addon->info()->title, 'fas fa-trash-alt')
					->body($purge)
					->submit('Supprimer', 'primary')
					->cancel()
					->callback($form);
	}
}


