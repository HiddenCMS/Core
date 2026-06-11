<?php
/**
 * https://neofr.ag
 * @author: Micha?l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Access\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Admin_Ajax extends Controller_Module
{
	public function _edit($module, $type, $access, $id, $title = NULL)
	{
		$this	->css('access')
				->js('access')
				->css('table')
				->js('table');

		$reset = $this->button()
						->tooltip($this->lang('R?initialiser toutes les permissions'))
						->icon('fas fa-sync')
						->color('info access-reset')
						->compact()
						->outline()
						->data([
							'module' => $module->info()->name,
							'type'   => $type,
							'id'     => $id
						]);

		$body = '<div class="module-access access-modal-layout">'
				.'<div class="ui stackable grid">'
					.'<div class="sixteen wide mobile seven wide computer column access-modal-master">'
						.'<div class="access-modal-section">'
							.'<div class="access-modal-section-header">'
								.'<span>'.icon('fas fa-unlock-alt').' '.$this->lang('Permissions').'</span>'
								.'<span>'.$reset.'</span>'
							.'</div>'
							.'<div class="access-modal-section-body">'
								.$this->view('index', [
									'loader' => $module,
									'module' => $module->info()->name,
									'type'   => $type,
									'id'     => $id,
									'access' => $access
								])
							.'</div>'
						.'</div>'
					.'</div>'
				.'</div>'
			.'</div>';

		return $this	->modal($title ?: $this->lang('Gestion des permissions'), $module->info()->icon)
						->set_id('access-permissions-modal-'.url_title($module->info()->name.'-'.$type.'-'.$id))
						->body($body, FALSE)
						->large()
						->close();
	}

	public function edit($module, $type, $access, $id, $title = NULL)
	{
		return $this->_edit($module, $type, $access, $id, $title);
	}

	public function index($action, $title, $icon, $module_name, $id)
	{
		$groups = [];

		foreach (array_keys($this->groups()) as $group_id)
		{
			$groups[$group_id] = HB()->access($module_name, $action, $id, $group_id);
		}

		return '<div class="sixteen wide mobile nine wide computer column access-modal-details">'
				.'<div class="access-modal-section">'
					.'<div class="access-modal-section-header">'
						.'<span>'.icon($icon).' '.$title.'</span>'
						.'<span>'.$this->button()->tooltip($this->lang('Utilisateurs'))->icon('fas fa-users')->color('info access-users')->compact()->outline().'</span>'
					.'</div>'
					.'<div class="access-modal-section-body">'
						.$this->view('details', [
							'groups' => $groups
						])
					.'</div>'
				.'</div>'
			.'</div>';
	}

	public function update($module_name, $action, $id, $groups, $user, $title, $icon)
	{
		$output = [];

		if ($groups)
		{
			$count      = array_count_values($groups);
			$authorized = isset($count[0]) ? $count[0] >= $count[1] : 0;

			$this->model()	->delete($module_name, $action, $id, 'group')
							->add($module_name, $action, $id, 'group', array_keys(array_filter($groups, function($a) use ($authorized){
								return $a == $authorized;
							})), $authorized);
		}
		else if ($user)
		{
			$this->model()->delete($module_name, $action, $id, 'user', $user_id = array_keys($user)[0]);

			if (($authorized = current($user)) != -1)
			{
				$this->model()->add($module_name, $action, $id, 'user', $user_id, $authorized);
			}
		}

		$this->access->reload();

		if ($groups)
		{
			$output['details'] = $this->index($action, $title, $icon, $module_name, $id);
		}
		else if ($user)
		{
			$output['user_authorized'] = $authorized = $this->access($module_name, $action, $id, NULL, $user_id);
			$output['user_forced']     = is_int($authorized);
		}

		$output['count'] = $this->access->count($module_name, $action, $id);

		return $this->json($output);
	}

	public function users($action, $title, $icon, $module_name, $id)
	{
		$this	->table()
				->add_columns([
						[
						'title'   => $this->lang('Membre'),
						'content' => function($data){
							return HB()->user->link($data['user_id'], $data['username']).'<span data-user-id="'.$data['user_id'].'"></span>';
						},
						'sort'    => function($data){
							return $data['username'];
						},
						'search'  => function($data){
							return $data['username'];
						}
					],
					[
						'title'   => $this->lang('Groupes'),
						'content' => function($data){
							return HB()->groups->user_groups($data['user_id']);
						},
						'sort'    => function($data){
							return HB()->groups->user_groups($data['user_id'], FALSE);
						},
						'search'  => function($data){
							return HB()->groups->user_groups($data['user_id'], FALSE);
						}
					],
					[
						'content' => function($data){
							$output = '';

							if (is_int($data['active']))
							{
								$output = '<a class="access-revoke" href="#" data-toggle="tooltip" title="'.$this->lang('Remettre en automatique').'">'.icon('fas fa-thumbtack').'</a>';
							}

							return '<td class="access-status">'.$output.'</td>';
						},
						'sort'    => function($data){
							return $data['active'] === NULL;
						},
						'size'    => TRUE,
						'td'      => FALSE
					],
					[
						'title'   => '<div class="text-center" data-toggle="tooltip" title="'.$this->lang('Membre autoris?').'">'.icon('fas fa-check').'</i></div>',
						'content' => function($data){
							return $this->view('radio', [
								'class'  => 'success',
								'active' => $data['active']
							]);
						},
						'td'      => FALSE
					],
					[
						'title'   => '<div class="text-center" data-toggle="tooltip" title="'.$this->lang('Membre exclu').'">'.icon('fas fa-ban').'</i></div>',
						'content' => function($data){
							static $admins;

							if ($admins === NULL)
							{
								$admins = HB()->groups()['admins']['users'];
							}

							return in_array($data['user_id'], $admins) ? '<td></td>' : $this->view('radio', [
								'class'  => 'danger',
								'active' => !$data['active'] && $data['active'] !== NULL
							]);
						},
						'td'      => FALSE
					]
				])
				->data($this->db->select('id as user_id', 'username')->from('user')->where('deleted', FALSE)->get())
				->preprocessing(function($row) use ($module_name, $action, $id){
					$row['active'] = HB()->access($module_name, $action, $id, NULL, $row['user_id']);
					return $row;
				})
				->sort_by(3, SORT_DESC)
				->sort_by(2, SORT_ASC)
				->sort_by(1, SORT_ASC);

		return $this->view('users', [
			'title' => $title,
			'icon'  => $icon,
			'users' => $this->table()->display()
		]);
	}

	public function reset($module, $type, $id)
	{
		$this->access	->delete($module, $id)
						->init($module, $type, $id);
	}
}



