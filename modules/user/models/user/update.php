<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\User\Models\User;

class Update extends \HB\HiddenCMS\Actions\Update
{
	protected $_ajax = FALSE;

	protected function check($user)
	{
		return !$user->deleted;
	}

	protected function action($user)
	{
		$form_groups = $this
			->form()
			->add_rules([
				'groups' => [
					'type'   => 'checkbox',
					'values' => array_filter($this->groups(), function($group){
						return !$group['auto'] || $group['auto'] == 'HiddenCMS' || $group['users'] !== NULL;
					}),
					'rules'  => 'required'
				]
			])
			->save();

		if ($form_groups->is_valid($post))
		{
			$this->db	->where('user_id', $user->id)
						->delete('users_groups');

			$this->db	->where('id', $user->id)
						->update('user', [
							'admin' => FALSE
						]);

			if (in_array('admins', $post['groups']))
			{
				$this->db	->where('id', $user->id)
							->update('user', [
								'admin' => TRUE
							]);
			}

			foreach ($post['groups'] as $group_id)
			{
				if ($this->groups()[$group_id]['auto'])
				{
					continue;
				}

				$this->db->insert('users_groups', [
					'user_id'  => $user->id,
					'group_id' => $group_id
				]);
			}

			notify((string)$this->lang('Member groups updated'));

			redirect_back('admin/user');
		}

		$this->module()	->title($this->lang('Member edit'))
						->subtitle($user->username)
						->css('groups')
						->css('admin/user_editor')
						->js('groups')
						->js('admin/user_editor');

		$account = $this->row()
					->append(
						$this	->col()
								->size('col-12 col-lg-7')
								->append(
									$this	->form2('username email new_password', $user)
											->success(function($user){
												if ($user->password_new)
												{
													$user->set_password($user->password_new);
												}

												$user->update();

												notify($this->lang('Member updated'));
												redirect('admin/user/user/update/'.$user->url());
											})
											->panel()
											->title('Member')
								)
					)
					->append(
						$this	->col()
								->size('col-12 col-lg-5')
								->append(
									$this	->panel()
											->heading($this->lang('Groups'), 'fas fa-users')
											->body($this->view('admin/groups', [
												'user_id' => $user->id,
												'form_id' => $form_groups->token()
											]))
								)
					);

		$images = $this->row()
					->append(
						$this->col()->size('col-12 col-lg-6')
								->append(
									$this	->form2('avatar', $user->profile())
											->panel()
											->title('Avatar', 'fas fa-user-circle')
								)
					)
					->append(
						$this->col()->size('col-12 col-lg-6')
								->append(
									$this	->form2('cover', $user->profile())
											->panel()
											->title('Cover photo', 'far fa-image')
								)
					);

		$tabs = [
			'account' => ['title' => 'Compte', 'icon' => 'fas fa-user', 'content' => $account],
			'profile' => ['title' => 'Profil', 'icon' => 'fas fa-pencil-alt', 'content' => $this->form2('profile', $user->profile())->panel()->title('Profile', 'fas fa-pencil-alt')],
			'links' => ['title' => (string)$this->lang('Links'), 'icon' => 'fas fa-globe', 'content' => $this->form2('profile_socials', $user->profile())->panel()->title('Links', 'fas fa-globe')],
			'images' => ['title' => 'Images', 'icon' => 'far fa-image', 'content' => $images]
		];
		if ($custom = $this->module('user')->model('fields')->profile_panel($user))
		{
			$tabs['custom'] = ['title' => (string)$this->lang('Custom fields'), 'icon' => 'fas fa-list', 'content' => $custom];
		}
		$tabs['sessions'] = ['title' => 'Sessions', 'icon' => 'fas fa-desktop', 'content' => $this->table2('session', $user->sessions(), (string)$this->lang('No active sessions'))->panel()->title('Active sessions', 'fas fa-globe')];

		return $this->view('admin/user_editor', ['tabs' => $tabs]);
	}
}


