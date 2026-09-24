<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\User;

use HB\HiddenCMS\Addons\Module;

class User extends Module
{
	public function permissions()
	{
		return [
			'default' => [
				'access' => [[
					'title'  => $this->lang('Users'),
					'icon'   => 'fas fa-users',
					'access' => [
						'view_users' => [
							'title' => $this->lang('View users'),
							'icon'  => 'far fa-eye',
							'admin' => TRUE
						],
						'create_users' => [
							'title' => $this->lang('Create users'),
							'icon'  => 'fas fa-user-plus',
							'admin' => TRUE
						],
						'edit_users' => [
							'title' => $this->lang('Edit users'),
							'icon'  => 'fas fa-user-edit',
							'admin' => TRUE
						],
						'delete_users' => [
							'title' => $this->lang('Delete users'),
							'icon'  => 'fas fa-user-times',
							'admin' => TRUE
						],
						'assign_user_groups' => [
							'title' => $this->lang('Assign user groups'),
							'icon'  => 'fas fa-user-tag',
							'admin' => TRUE
						],
						'manage_groups' => [
							'title' => $this->lang('Manage groups'),
							'icon'  => 'fas fa-users-cog',
							'admin' => TRUE
						],
						'manage_sessions' => [
							'title' => $this->lang('Manage sessions'),
							'icon'  => 'fas fa-desktop',
							'admin' => TRUE
						]
					]
				]]
			]
		];
	}

	protected function __info()
	{
		return [
			'title'       => $this->lang('User'),
			'description' => '',
			'icon'        => 'fas fa-user',
			'reserved_route' => 'user',
			'link'        => 'https://neofr.ag',
			'author'      => 'Michaël BILCOT & Jérémy VALENTIN <contact@HiddenCMS.com>',
			'license'     => 'LGPLv3 <https://neofr.ag/license>',
			'admin'       => FALSE,
			'front'       => TRUE,
			'outline_routes' => [
				'login'           => $this->lang('Sign in'),
				'register'        => $this->lang('Sign up'),
				'lost-password'   => $this->lang('Forgot your password?'),
				'lost-password/*' => $this->lang('Password reset')
			],
			'routes'      => [
				//Index
				'login'                                      => 'login',
				'register'                                   => 'register',
				'lost-password'                              => 'lost_password_request',
				'lost-password/{url_title}'                  => 'lost_password',
				'sessions{pages}'                            => 'sessions',
				'auth{pages}'                                => '_auth',
				'sessions/delete/{key_id}'                   => '_session_delete',
				'messages'                                   => '_messages_inbox',
				'messages/sent'                              => '_messages_sent',
				'messages/archives'                          => '_messages_archives',
				'messages/{id}/{url_title}(?:/{url_title})?' => '_messages_read',
				'messages/compose(?:/{id}/{url_title})?'     => '_messages_compose',
				'messages/delete/{id}/{url_title}'           => '_messages_delete',
				'{id}/{url_title}'                           => '_member',
				'ajax/{id}/{url_title}'                      => '_member',
				//Admin
				'admin{pages}'                                   => 'index',
				'admin/create'                                   => 'create',
				'admin/fields(?:/{id})?'                          => 'fields',
				'admin/fields/delete/{id}'                        => 'field_delete',
				'admin/groups/add'                               => '_groups_add',
				'admin/groups/edit/(admins|members|visitors)'    => '_groups_edit',
				'admin/groups/edit/{url_title}-{id}/{url_title}' => '_groups_edit',
				'admin/groups/edit/{id}/{url_title}'             => '_groups_edit',
				'admin/groups/delete/{id}/{url_title}'           => '_groups_delete',
				'admin/ajax/groups/sort'                         => '_groups_sort',
				'admin/sessions{pages}'                          => '_sessions',
				'admin/sessions/delete/{url_title}'              => '_sessions_delete'
			]
		];
	}
}


