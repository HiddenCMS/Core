<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\User\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Admin extends Controller_Module
{
	public function index($members)
	{
		$this	->title('Membres / Groupes')
				->icon('fas fa-users');

		$table_groups = $this
			->table()
			->add_columns([
				[
					'content' => function($data){
						return $data['auto'] != 'HiddenCMS' ? $this->button_sort($data['data_id'], 'admin/ajax/user/groups/sort') : NULL;
					},
					'size'    => TRUE
				],
				[
					'content' => function($data){
						return HB()->groups->display($data['data_id']);
					},
					'search'  => function($data){
						return HB()->groups->display($data['data_id'], FALSE, FALSE);
					}
				],
				[
					'content' => function($data){
						return $data['hidden'] ? $this->button()->icon('far fa-eye-slash')->tooltip('Groupe caché') : NULL;
					},
					'size'    => TRUE
				],
				[
					'content' => function($data){
							return $this->button_update('admin/user/groups/edit/'.$data['url']);
					},
					'size'    => TRUE
				],
				[
					'content' => function($data){
						if (!$data['auto'])
						{
							return $this->button_delete('admin/user/groups/delete/'.$data['url']);
						}
					},
					'size'    => TRUE
				]
			])
			->data($this->groups())
			->pagination(FALSE)
			->save();

		return $this->row(
			$this->col(
				$this	->panel()
						->heading($this->lang('Groupes'), 'fas fa-users')
						->body($table_groups->display())
						->footer($this->button_create('admin/user/groups/add', $this->lang('Ajouter un groupe')))
						->size('col-12 col-lg-3')
			),
			$this->col(
				$this	->table2($members, 'Aucun membre')
						->col($this	->table_col()
									->title('Membre')
									->content('link')
									->sort('username')
						)
						->col($this	->table_col()
									->title('Email')
									->content(function($user){
										return '<a href="mailto:'.$user->email.'">'.$user->email.'</a>';
									})
									->sort('email')
						)
						->col('Groupes', 'groups')
						->col($this	->table_col()
									->title('Inscrit depuis le')
									->content('registration_date')
									->sort('registration_date')
						)
						->col($this	->table_col()
									->title('Dernière activité')
									->content('last_activity_date')
									->sort('last_activity_date')
						)
						->update()
						->delete()
						->counter('COUNT(*)', '%s membre|%s membres')
						->panel()
						->title('Membres', 'fas fa-users')
						->footer_if($this->user->admin, $this->button_create('admin/user/create', 'Créer un utilisateur'))
						->footer_if($this->user->admin, $this->button()->title('Champs et connexion')->icon('fas fa-sliders-h')->color('secondary')->url('admin/user/fields'))
						->size('col-12 col-lg-9')
			)
		);
	}

	public function create()
	{
		$this->title('Créer un utilisateur')->icon('fas fa-user-plus');
		return $this->form2('username email password_required custom_fields', $this->model2('user'))
			->success(function($user, $form){
				try
				{
					// An admin-created account does not wait for registration e-mail validation.
					$user->set('data', $user->data->set('registration_verified', TRUE));
					$this->model('fields')->save_user($user, TRUE);
				}
				catch (\InvalidArgumentException $e) { $form->error($e->getMessage()); return; }
				notify('Utilisateur créé');
				redirect('admin/user/user/update/'.$user->url());
			})
			->submit('Créer')->back('admin/user')->panel()->title('Nouvel utilisateur', 'fas fa-user-plus');
	}

	public function fields($field)
	{
		$this->title('Champs et connexion')->icon('fas fa-sliders-h')->js('user_fields');
		$model = $this->model('fields');
		$choices = ['username' => 'Pseudo', 'email' => 'Adresse e-mail'];
		foreach ($model->all() as $item)
		{
			if ($item['type'] === 'text') $choices['field:'.$item['id']] = utf8_htmlentities($item['label']);
		}
		$login = $this->form2()
			->rule($this->form_select('identifier')->title('Identifiant de connexion')->data($choices)->value($model->identifier())->required())
			->success(function($data, $form) use ($model){
				try { $model->set_identifier($data['identifier']); }
				catch (\InvalidArgumentException $e) { $form->error($e->getMessage()); return; }
				notify('Identifiant de connexion enregistré');
				refresh();
			})
			->submit('Enregistrer')->panel()->title('Connexion', 'fas fa-sign-in-alt');
		$options = [];
		foreach ($field['options'] ?? [] as $key => $label) $options[] = $key.'|'.$label;
		$form = $this->form2()
			->rule($this->form_text('label')->title('Libellé')->value(utf8_htmlentities($field['label'] ?? ''))->required())
			->rule($this->form_text('name')->title('Nom technique')->value($field['name'] ?? '')->required()->read_only_if($field))
			->rule($this->form_select('type')->title('Type')->data($model->types())->value($field['type'] ?? 'text')->required()->disabled_if($field))
			->rule($this->form_textarea('options')->title('Choix (un par ligne : valeur|libellé)')->rows(5)->value(utf8_htmlentities(implode("\n", $options))))
			->rule($this->form_checkbox('required')->data(['1' => 'Champ obligatoire'])->value(!empty($field['required']) ? ['1'] : []))
			->success(function($data, $form) use ($model, $field){
				try { $model->save_definition($data, $field['id'] ?? 0); }
				catch (\InvalidArgumentException $e) { $form->error($e->getMessage()); return; }
				notify('Champ enregistré');
				redirect('admin/user/fields');
			})
			->submit($field ? 'Enregistrer' : 'Ajouter')->back('admin/user')->panel()->title($field ? 'Modifier le champ' : 'Nouveau champ', 'fas fa-list');
		return $this->row(
			$this->col($this->panel()->heading('Champs personnalisés', 'fas fa-list')->body($this->view('admin/fields', ['fields' => $model->all(), 'types' => $model->types(), 'identifier' => $model->identifier()]))->footer($this->button_create('admin/user/fields', 'Ajouter un champ')), $login)->size('col-12 col-lg-5'),
			$this->col($form)->size('col-12 col-lg-7')
		);
	}

	public function field_delete($field)
	{
		return $this->form2()
			->rule($this->form_checkbox('confirm')->data(['1' => 'Supprimer le champ '.utf8_htmlentities($field['label']).' et les valeurs enregistrées'])->required())
			->success(function($data, $form) use ($field){
				try { $this->model('fields')->delete_definition($field['id']); }
				catch (\InvalidArgumentException $e) { $form->error($e->getMessage()); return; }
				notify('Champ supprimé');
				redirect('admin/user/fields');
			})
			->submit('Supprimer', 'danger')->back('admin/user/fields')->panel()->title('Supprimer le champ', 'fas fa-trash');
	}

	public function _groups_add()
	{
		$this	->title($this->lang('Groupes'))
				->subtitle($this->lang('Ajouter'))
				->form()
				->add_rules('groups')
				->add_back('admin/user')
				->add_submit($this->lang('Ajouter'));

		if ($this->form()->is_valid($post))
		{
			$this->model('groups')->add_group(
				$post['title'],
				$post['color'],
				$post['icon'],
				in_array('on', $post['hidden']),
				$this->config->lang->info()->name
			);

			notify($this->lang('Groupe ajouté'));

			redirect_back('admin/user');
		}

		return $this->panel()
					->heading($this->lang('Ajouter un groupe'), 'fas fa-users')
					->body($this->form()->display())
					->size('col-12');
	}

	public function _groups_edit($group_id, $name, $title, $color, $icon, $hidden, $auto)
	{
		$this	->title($this->lang('Groupes'))
				->subtitle($this->lang('Éditer'))
				->form()
				->add_rules('groups', [
					'title'  => $title,
					'color'  => $color,
					'icon'   => $icon,
					'hidden' => $hidden,
					'auto'   => $auto
				])
				->add_back('admin/user')
				->add_submit($this->lang('Éditer'));

		if ($this->form()->is_valid($post))
		{
			if ($group_id)
			{
				$this->model('groups')->edit_group(
					$group_id,
					!$auto ? $post['title'] : NULL,
					$post['color'],
					$post['icon'],
					in_array('on', $post['hidden']),
					$this->config->lang->info()->name,
					$auto
				);
			}
			else
			{
				$this->db->insert('groups', [
					'name'  => $name,
					'color' => $post['color'],
					'icon'  => $post['icon'],
					'auto'  => TRUE
				]);
			}

			notify($this->lang('Groupe modifié'));

			redirect_back('admin/user');
		}

		return $this->panel()
					->heading($this->lang('Éditer un groupe'), 'fas fa-users')
					->body($this->form()->display())
					->size('col-12');
	}

	public function _groups_delete($group_id, $title)
	{
		$this	->title($this->lang('Confirmation de suppression'))
				->form()
				->confirm_deletion($this->lang('Confirmation de suppression'), $this->lang('Êtes-vous sûr(e) de vouloir supprimer le groupe <b>%s</b> ?', $title));

		if ($this->form()->is_valid())
		{
			$this->db	->where('group_id', $group_id)
						->delete('groups');

			$this->access->revoke($group_id);

			return 'OK';
		}

		return $this->form()->display();
	}

	public function _sessions($sessions)
	{
		return $this->title($this->lang('Sessions'))
					->icon('fas fa-globe')
					->table2('session', $sessions, 'Aucune session active')
					->panel()
					->title('Liste des sessions actives', 'fas fa-bars');
	}

	public function _sessions_delete($session_id, $username)
	{
		$this	->title($this->lang('Confirmation de suppression'))
				->form()
				->confirm_deletion($this->lang('Confirmation de suppression'), $this->lang('Êtes-vous sûr(e) de vouloir supprimer la session de l\'utilisateur <b>%s</b> ?', $username));

		if ($this->form()->is_valid())
		{
			$this->db	->where('id', $session_id)
						->delete('session');

			return 'OK';
		}

		return $this->form()->display();
	}
}


