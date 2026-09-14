<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\User\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Index extends Controller_Module
{
	public function login()
	{
		$authenticators = HB()->model2('addon')
								->get('authenticator')
								->filter(function($authenticator){
									return $authenticator->is_setup();
								})
								->sort(function($a, $b){
									return $a->settings()->order - $b->settings()->order;
								});

		$this	->title('Sign in')
				->icon('fas fa-sign-in-alt')
				->css('auth-page');

		return $this->view('login', [
			'form'           => $this->form2('login')->panel()->style('user-auth-form-panel'),
			'authenticators' => $authenticators
		]);
	}

	public function register()
	{
		$form = $this->form2(!empty($this->config->registration_charte) ? 'username password_required email custom_fields charte' : 'username password_required email custom_fields', $this->model2('user'))
			->info(privacy_notice())
			->compact()
			->captcha(FALSE, 'register')
			->success(function($user, $form){
				if ($this->config->registration_validation)
				{
					$sent = $this->anti_flood()
						->email
						->to($user->email)
						->subject((string)$this->lang('Validate your account'))
						->message(function() use ($user){
							return [
								'content' => $this->lang('Hello %s,<br /><br />To confirm your registration on our website, please click the button below.', utf8_htmlentities($user->username)).'<br /><br /><div class="text-center"><a class="btn btn-primary" href="'.url('user/validation/'.$user->token()).'">'.$this->lang('Validate my account').'</a></div>'
							];
						})
						->send();

					if (!$sent)
					{
						$form->error((string)$this->lang('An error occurred while sending the message'));
						return;
					}

					notify((string)$this->lang('Message sent'));
				}

				try
				{
					$this->model('fields')->save_user($user, TRUE);
				}
				catch (\InvalidArgumentException $e)
				{
					$form->error($e->getMessage());
					return;
				}

				if ($this->config->welcome && $this->config->welcome_user_id && !empty($this->config->welcome_title) && !empty($this->config->welcome_content))
				{
					$this->model('messages')->insert_message($user->username, $this->config->welcome_title, str_replace('[pseudo]', '@'.$user->username, $this->config->welcome_content), TRUE);
				}

				notify((string)$this->lang('Your account has been created. Welcome!'));
				$this->session->login($user);
				redirect();
			})
			->panel()
			->style('user-auth-form-panel');

		return $this->_auth_page(
			(string)$this->lang('Create an account'),
			'fas fa-user-plus',
			(string)$this->lang('Join the site in a few moments.'),
			$form,
			[['url' => url('user/login'), 'title' => (string)$this->lang('Already registered? Sign in')]]
		);
	}

	public function lost_password_request()
	{
		$form = $this->form2()
			->compact()
			->rule($this->form_email('email')->title('Email address')->required())
			->success(function($data, $form){
				$user = $this->db
					->collection('user')
					->where('deleted', FALSE)
					->where('email', $data['email'])
					->row();

				if (!$user())
				{
					$form->error($this->lang('Email address not found'));
					return;
				}

				$sent = $this->anti_flood()
					->email
					->to($data['email'])
					->subject((string)$this->lang('Password reset'))
					->message(function() use ($user){
						return [
							'content' => $this->lang('Hello %s,', utf8_htmlentities($user->username)).'<br /><br />'.$this->lang('You requested a password reset. Click the button below to choose a new password.').'<br /><br /><div class="text-center"><a class="btn btn-primary" href="'.url('user/lost-password/'.$user->token()).'">'.$this->lang('Reset my password').'</a></div>'
						];
					})
					->send();

				if (!$sent)
				{
					$form->error((string)$this->lang('An error occurred while sending the message'));
					return;
				}

				notify((string)$this->lang('A password reset link has been sent to you.'));
				redirect('user/login');
			})
			->panel()
			->style('user-auth-form-panel');

		return $this->_auth_page(
			(string)$this->lang('Forgot your password?'),
			'fas fa-unlock-alt',
			(string)$this->lang('Enter your email address to receive a reset link.'),
			$form,
			[['url' => url('user/login'), 'title' => (string)$this->lang('Back to sign-in')]]
		);
	}

	public function lost_password($token)
	{
		$form = $this->form2('password_required')
			->compact()
			->success(function($data) use ($token){
				$token->delete()
					->user
					->set_password($data['password'])
					->update();

				notify((string)$this->lang('New password saved'));
				$this->session->login($token->user);
				redirect();
			})
			->panel()
			->style('user-auth-form-panel');

		return $this->_auth_page(
			(string)$this->lang('Choose a new password'),
			'fas fa-key',
			(string)$this->lang('Enter and confirm your new password.'),
			$form
		);
	}

	private function _auth_page($title, $icon, $description, $form, array $links = [])
	{
		$this->title($title)->icon($icon)->css('auth-page');

		return $this->view('auth', [
			'title'       => $title,
			'icon'        => $icon,
			'description' => $description,
			'form'        => $form,
			'links'       => $links
		]);
	}

	public function index()
	{
		$this->css('front');

		return $this->title((string)$this->lang('My activity'))
					->icon('far fa-star')
					->row([
						$this->col(
							$this	->panel()
									->heading('Mon profil')
									->body($this->user->view('profile')),
							$this->_panel_navigation()
						)->size('col-4'),
						$this->col(
							$this->row($this->col($this->panel()->body($this->_panel_infos()))),
							$this	->row()
									->append($this	->col()
													->size('col-6')
													->append($this	->panel()
																	->heading('Messagerie')
																	->body($this->view('index'))
													)
									)
									->append($this	->col()
													->size('col-6')
													->append($this->_panel_activities())
									)
						)->size('col-8')
					]);
	}

	public function account($sessions)
	{
		$this->css('front');

		$export = $this->form2('current_password', $this->user)
			->info((string)$this->lang('Download a structured copy of the data associated with your account. Files you own are included in the archive. Passwords, tokens and session identifiers are never exported.'))
			->success(function(){
				$file = $this->model('privacy')->archive($this->user);
				$name = 'hiddencms-personal-data-'.date('Y-m-d').'.zip';
				while (ob_get_level()) ob_end_clean();
				header('Content-Type: application/zip');
				header('Content-Disposition: attachment; filename="'.$name.'"');
				header('Content-Length: '.filesize($file));
				header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
				header('Pragma: no-cache');
				header('X-Content-Type-Options: nosniff');
				readfile($file);
				@unlink($file);
				exit;
			})
			->submit((string)$this->lang('Download my data'), 'primary')
			->panel()
			->title((string)$this->lang('My personal data'), 'fas fa-file-archive');

		$privacy = $this->model('privacy');
		$request = $privacy->erasure_request($this->user);
		if ($request && empty($request['completed_at']))
		{
			$erasure = $this->form2('current_password', $this->user)
				->info('<div class="ui warning message"><div class="header">'.$this->lang('Request registered').'</div><p>'.$this->lang('Your account will be anonymized from <strong>%s</strong>. You can cancel the request until then.', date('Y-m-d H:i', strtotime($request['execute_after']))).'</p></div>')
				->success(function($user) use ($privacy){
					$privacy->cancel_erasure($user);
					notify((string)$this->lang('Your account deletion request has been cancelled.'), 'success');
					refresh('user/account');
				})
				->submit((string)$this->lang('Cancel the request'), 'primary')
				->panel()
				->title((string)$this->lang('Delete my account'), 'fas fa-user-times');
		}
		else
		{
			$erasure = $this->form2('current_password', $this->user)
				->info('<div class="ui negative message"><div class="header">'.$this->lang('Delayed deletion').'</div><p>'.$this->lang('After the cancellation period, your account information, profile, sign-ins and personal files will be deleted. Contributions and messages needed for discussions will remain visible under an anonymized name.').'</p></div>')
				->rule($this->form_checkbox('confirm_erasure')->data([
					'1' => (string)$this->lang('I understand that this operation will become irreversible after the waiting period.')
				])->required()->inline(FALSE))
				->success(function($user) use ($privacy){
					try
					{
						$request = $privacy->request_erasure($user);
						notify((string)$this->lang('Request registered. You can cancel it until %s.', date('Y-m-d H:i', strtotime($request['execute_after']))), 'success');
					}
					catch (\Throwable $e)
					{
						notify($e->getMessage(), 'danger');
					}
					refresh('user/account');
				})
				->submit((string)$this->lang('Request deletion'), 'danger')
				->panel()
				->title((string)$this->lang('Delete my account'), 'fas fa-user-times');
		}

		return $this->row([
						$this->col(
							$this	->panel()
									->heading('Mon profil')
									->body($this->user->view('profile')),
							$this->_panel_navigation()
						)->size('col-4'),
						$this->col(
							$this->title('Sign in')
								->icon('fas fa-sign-in-alt')
								->breadcrumb()
								->form2('username current_password new_password email', $this->user)
								->success(function($user){
									if ($user->password_new)
									{
										$user->set_password($user->password_new);
									}
									else
									{
										$user->reset('password');
									}

									if ($user->has_changed('email') && $this->config->registration_validation)
									{
										//TODO
									}

									$user->update();

									notify($this->lang('Account information updated'));

									refresh();
								})
								->submit((string)$this->lang('Edit'))
								->panel()
								->title('Info de connexion'),
							$export,
							$erasure
						)->size('col-8')
					]);

					/* TODO
					->row()
					->append(
						$this	->col()
								->size('col-6')
								->append(
									$this
								)
					)
					->append(
						$this	->col()
								->size('col-6')
								->append(
									$this	->table2($sessions)
											->col(function($session){
												return user_agent($session->data->session->user_agent);
											})
											->col('Adresse IP', function($session){
												return geolocalisation($ip_address = $session->data->session->ip_address).'<span data-toggle="tooltip" data-original-title="'.$session->data->session->host_name.'">'.$ip_address.'</span>';
											})
											->col('Site référent', function($session){
												return $session->data->session->referer ? urltolink($session->data->session->referer) : $this->lang('Aucun');
											})
											->col('Date', function($session){
												return $session->data->session->date;
											})
											->col('Compte tiers', function($session){
												return $session->auth ? $session->auth : '';
											})
											->delete()
											->panel()
											->title('Sessions actives', 'fas fa-globe')
								)
								->append(
									$this	->form2()
											->rule($this->form_checkbox('delete')
														->data([
															'account'   => 'Je souhaite supprimer mon compte',
															//'keep_data' => 'J\'accepte que mes contributions soient conservées de façon anonyme'
														])
											)
											->form('current_password')
											->success(function($data){
												if (in_array('account', $data['delete']))
												{
													//TODO
													if (1 || in_array('keep_data', $data['delete']))
													{
														$this->user->set('deleted', TRUE)->update();
													}
													else
													{
														$this->user->delete();
													}

													HB()->collection('session')->where('user_id', $this->user->id)->update([
														'user_id' => NULL
													]);

													notify('Compte supprimé');

													redirect();
												}
											})
											->submit('Supprimer', 'danger')
											->panel()
											->title('Supprimer mon compte', 'fas fa-times')
								)
					);*/
	}

	public function profile()
	{
		$this->css('front');

		$this	->title('Profile')
				->icon('fas fa-pencil-alt')
				->breadcrumb();

		return $this->_layout(function($row){
			$row->append($this	->col()
								->size('col-12 col-lg-7')
								->append($this	->form2('profile', $this->user->profile())
												->panel()
								)
								->append($this	->form2('profile_socials', $this->user->profile())
												->panel()
												->title('Links', 'fas fa-globe')
								)
								->append($this->model('fields')->profile_panel($this->user))
				)
				->append($this	->col()
								->size('col-12 col-lg-5')
								->append($this	->form2('avatar', $this->user->profile())
												->panel()
												->title('Avatar', 'fas fa-user-circle')
								)
								->append($this	->form2('cover', $this->user->profile())
												->panel()
												->title('Cover photo', 'far fa-image')
								)
				);
		});
	}

	public function sessions($sessions)
	{
		$this->css('front');

		return $this->row([
						$this->col(
							$this	->panel()
									->heading('Mon profil')
									->body($this->user->view('profile')),
							$this->_panel_navigation()
						)->size('col-4'),
						$this->col(
							$this	->title('Historique des sessions')
									->icon('fas fa-history')
									->breadcrumb()
									->table2('session_history', $sessions, (string)$this->lang('No history'))
									->panel()
						)->size('col-8')
					]);
	}

	public function _session_delete($session_id)
	{
		$this	->title($this->lang('Confirm deletion'))
				->form()
				->confirm_deletion($this->lang('Confirm deletion'), $this->lang('Are you sure you want to delete the session of the user <b>%s</b>?'));

		if ($this->form()->is_valid())
		{
			$this->db	->where('id', $session_id)
						->delete('session');

			return 'OK';
		}

		return $this->form()->display();
	}

	public function auth($authenticator)
	{
		spl_autoload_register(function($name){
			if (preg_match('/^SocialConnect/', $name))
			{
				require_once 'lib/'.str_replace('\\', '/', $name).'.php';
			}
		});

		$service = new \SocialConnect\Auth\Service(
			new \SocialConnect\Common\Http\Client\Curl,
			new \SocialConnect\Provider\Session\HB($this->session), [
				'redirectUri' => $authenticator->static_url(),
				'provider'    => [
					$name = str_replace('_', '-', $authenticator->info()->name) => $authenticator->config()
				]
			]
		);

		$provider = $service->getProvider($name);

		if ($callback = $authenticator->data($params))
		{
			$data = array_merge(array_fill_keys(['id', 'username', 'avatar'], ''), $callback($provider->getIdentity($provider->getAccessTokenByRequestParameters($params))));

			if (($auth = $this->collection('auth')->where('authenticator_id', $authenticator->__addon->id)->where('key', $data['id'])->row()) && $auth->key == $data['id'])
			{
				if ($this->user->id != $auth->user->id)
				{
					$auth	->set_if($data['username'], 'username', $data['username'])
							->set_if($data['avatar'],   'avatar',   $data['avatar'])
							->update();

					$this->session->login($auth->user);
					$this->url->redirect(user_login_destination());
					return;
				}
			}
			else if ($this->user())
			{
				$auth	->set('user',          $this->user)
						->set('authenticator', $authenticator->__addon)
						->set('key',           $data['id'])
						->set_if($data['username'], 'username', $data['username'])
						->set_if($data['avatar'],   'avatar',   $data['avatar'])
						->create();

				notify($this->lang('Signed in using %s', $authenticator->info()->title));
			}
			else
			{
				$this->session->append('auth', 'providers', $authenticator->__addon->id.'-'.$data['id'], [$authenticator->__addon->id, $data]);

				notify($this->lang('Unknown %s account', $authenticator->info()->title), 'danger');
			}

			redirect();
		}

		$this->url->redirect($provider->makeAuthUrl());
	}

	public function _auth($auths)
	{
		return 'auth';
	}

	public function logout()
	{
		$this->session->logout();
		redirect();
	}

	public function _messages($messages, $allow_delete, $page_title, $page_icon, $box = 'inbox')
	{
		$this	->breadcrumb()
				->css('jquery.mCustomScrollbar.min')
				->js('jquery.mCustomScrollbar.min')
				->js('user');

		return $this->_layout(function($row) use ($messages, $allow_delete, $page_title, $page_icon, $box){
			$row->append($this	->col()
								->append($this	->panel()
												->body($this->view('messages', [
													'page_title'   => $page_title,
													'page_icon'    => $page_icon,
													'messages'     => $messages,
													'allow_delete' => $allow_delete,
													'box'          => $box
												]), FALSE)
												->style('card-group border-0')
								)
			);
		});
	}

	public function _messages_inbox($messages)
	{
		return $this->_messages($messages, TRUE, (string)$this->lang('Inbox'), 'fas fa-inbox');
	}

	public function _messages_sent($messages)
	{
		return $this->_messages($messages, FALSE, (string)$this->lang('Sent messages'), 'far fa-paper-plane', 'sent');
	}

	public function _messages_archives($messages)
	{
		return $this->_messages($messages, FALSE, 'Archives', 'fas fa-archive', 'archives');
	}

	public function _messages_read($message_id, $title, $replies, $box, $allow_delete = FALSE)
	{
		$this	->css('jquery.mCustomScrollbar.min')
				->js('jquery.mCustomScrollbar.min')
				->js('user');

		if ($box == 'inbox')
		{
			$page_title   = $this->lang('Inbox');
			$page_icon    = 'fas fa-inbox';
			$allow_delete = TRUE;
		}
		else if ($box == 'sent')
		{
			$page_title = $this->lang('Sent messages');
			$page_icon  = 'far fa-paper-plane';
		}
		else if ($box == 'archives')
		{
			$page_title = $this->lang('Archives');
			$page_icon  = 'fas fa-archive';
		}

		$form_reply = $this	->form()
							->add_rules([
								'message' => [
									'type'  => 'editor',
									'rules' => 'required'
								]
							])
							->fast_mode(TRUE)
							->add_submit((string)$this->lang('Send message'))
							->save();

		if ($form_reply->is_valid($post))
		{
			$this->model('messages')->reply($message_id, $post['message']);

			redirect('user/messages/'.$message_id.'/'.url_title($title));
		}

		return $this->panel()
					->body($this->view('messages', [
						'page_title'   => $page_title,
						'page_icon'    => $page_icon,
						'messages'     => $this->model('messages')->get_messages_inbox($box),
						'allow_delete' => $allow_delete,
						'message_id'   => $message_id,
						'title'        => $title,
						'replies'      => $replies,
						'form_reply'   => $form_reply,
						'box'          => $box
					]), FALSE)
					->style('card-group border-0');
	}

	public function _messages_compose($username)
	{
		$this	->title((string)$this->lang('New private message'))
				->icon('far fa-envelope')
				->breadcrumb()
				->form()
				->add_rules([
					'title' => [
						'label' => 'Sujet du message',
						'type'  => 'text',
						'rules' => 'required'
					],
					'recipients' => [
						'label'       => 'Destinataires',
						'value'       => $username,
						'type'        => 'text',
						'rules'       => 'required',
						'description' => (string)$this->lang('Separate multiple recipients with a <b>;</b> <small>(semicolon)</small>')
					],
					'message' => [
						'label' => 'Mon message',
						'type'  => 'editor',
						'rules' => 'required'
					]
				])
				->add_submit((string)$this->lang('Send'));

		if ($this->form()->is_valid($post))
		{
			if ($message_id = $this->model('messages')->insert_message($post['recipients'], $post['title'], $post['message']))
			{
				redirect('user/messages/'.$message_id.'/'.url_title($post['title']));
			}
		}

		return $this->_layout(function($row){
			$row->append($this	->col()
								->append($this	->panel()
												->heading()
												->body($this->form()->display())
								)
			);
		});
	}

	public function _messages_delete($message_id, $title)
	{
		$this	->title($this->lang('Delete message'))
				->subtitle($title)
				->form()
				->confirm_deletion($this->lang('Confirm deletion'), $this->lang('Are you sure you want to delete message <b>%s</b>?', utf8_htmlentities($title)));

		if ($this->form()->is_valid())
		{
			$this->db	->where('user_id', $this->user->id)
						->where('message_id', $message_id)
						->update('users_messages_recipients', [
							'date'    => now(),
							'deleted' => TRUE
						]);

			return 'OK';
		}

		return $this->form()->display();
	}

	public function _member($user)
	{
		$this->css('front');

		return $this->title($user->username)
					->breadcrumb('Profil')
					->breadcrumb($user->username)
					->row()
					->append($this	->col()
									->size('col-4 user-col')
									->append($this	->panel()
													->body($user->view('profile'))
									)
					)
					->append($this	->col()
									->size('col-8')
									->append($this	->panel()
													->body($this->_panel_infos($user))
									)
									->append($this->_panel_activities($user->id))
									->append($this->panel_back())
					);
	}

	public function _panel_profile(&$user_profile = NULL)
	{
		$this->css('profile');

		return $this->panel()
					->heading('Mon profil', 'fas fa-user')
					->body($this->view('profile', $user_profile = $this->model()->get_user_profile($this->user->id)))
					->size('col-4 col-lg-3');
	}

	public function _panel_navigation($output = 'vertical')
	{
		$navigation = [
			'panel' => TRUE,
			'links' => [
				[
					'title' => 'Mon espace',
					'icon'  => 'fas fa-user',
					'url'   => 'user'
				],
				[
					'title' => 'Info de connexion',
					'icon'  => 'fas fa-sign-in-alt',
					'url'   => 'user/account'
				],
				[
					'title' => (string)$this->lang('Edit my profile'),
					'icon'  => 'fas fa-pencil-alt',
					'url'   => 'user/profile'
				],
				[
					'title' => (string)$this->lang('Private messaging'),
					'icon'  => 'far fa-envelope',
					'url'   => 'user/messages'
				],
				[
					'title' => (string)$this->lang('Manage my sessions'),
					'icon'  => 'fas fa-globe',
					'url'   => 'user/sessions'
				],
				[
					'title' => (string)$this->lang('Sign out'),
					'icon'  => 'fas fa-times',
					'url'   => 'user/logout'
				]
			]
		];

		return $this->widget('navigation')->output($output, $navigation);
	}

	public function _panel_infos($user = NULL)
	{
		return $this->view('infos', [
			'user' => $user ?: $this->user
		]);
	}

	private function _panel_activities($user_id = NULL)
	{
		$this	->css('activities')
				->js('user')
				->css('jquery.mCustomScrollbar.min')
				->js('jquery.mCustomScrollbar.min');

		if ($user_id === NULL)
		{
			$user_id = $this->user->id;
		}

		$user_activity = [];

		if ($forum = $this->module('forum'))
		{
			$categories = array_filter($this->db->select('category_id')->from('forum_categories')->get(), function($a){
				return $this->access('forum', 'category_read', $a);
			});

			if ($categories)
			{
				$user_activity = $this->db	->select('m.message_id', 'm.topic_id', 't.title', 'u.id as user_id', 'u.username', 'up.avatar', 'up.signature', 'up.sex', 'u.admin', 'm.message', 'UNIX_TIMESTAMP(m.date) as date')
											->from('forum_messages m')
											->join('forum_topics   t',  'm.topic_id  = t.topic_id')
											->join('forum          f',  't.forum_id  = f.forum_id')
											->join('forum          f2', 'f.parent_id = f2.forum_id AND f.is_subforum = "1"')
											->join('user           u',  'm.user_id   = u.id AND u.deleted = "0"')
											->join('user_profile   up', 'u.id        = up.id')
											->where('m.user_id', $user_id)
											->where('IFNULL(f2.parent_id, f.parent_id)', $categories)
											->order_by('m.date DESC')
											->limit(10)
											->get();
			}
		}

		return $this->panel()
					->heading((string)$this->lang('Recent activity'))
					->body($this->view('activity', [
						'user_activity' => $user_activity
					]));
	}

	private function _layout($callback)
	{
		$callback($row = $this->row());

		return $this->array()
					->append($this->row($this->col($this->_panel_navigation('index'))))
					->append($row);
	}
}


