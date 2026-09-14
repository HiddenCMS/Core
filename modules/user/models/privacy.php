<?php

namespace HB\Modules\User\Models;

use HB\HiddenCMS\Loadables\Model;
use RuntimeException;
use Throwable;
use ZipArchive;

class Privacy extends Model
{
	const ERASURE_DELAYS = [7, 14, 30];

	public function erasure_delay()
	{
		$days = (int)($this->config->privacy_erasure_delay ?? 30);
		return in_array($days, self::ERASURE_DELAYS, TRUE) ? $days : 30;
	}

	public function erasure_request($user)
	{
		$row = $this->db->from('user_erasure_request')->where('user_id', (int)$user->id)->row(FALSE);
		if (!empty($row['result'])) $row['result'] = $this->storage->decode($row['result'], []);
		return $row ?: NULL;
	}

	public function request_erasure($user)
	{
		$this->assert_erasure_allowed($user);
		if (($request = $this->erasure_request($user)) && empty($request['completed_at'])) return $request;

		$now = new \DateTimeImmutable();
		$data = [
			'user_id'       => (int)$user->id,
			'requested_at'  => $now->format('Y-m-d H:i:s'),
			'execute_after' => $now->modify('+'.$this->erasure_delay().' days')->format('Y-m-d H:i:s'),
			'completed_at'  => NULL,
			'result'        => NULL
		];

		if ($request)
		{
			$this->db->where('user_id', (int)$user->id)->update('user_erasure_request', $data);
		}
		else
		{
			$this->db->insert_checked('user_erasure_request', $data);
		}

		return $data;
	}

	public function cancel_erasure($user)
	{
		$request = $this->erasure_request($user);
		if (!$request || !empty($request['completed_at'])) return FALSE;
		$this->db->where('user_id', (int)$user->id)->delete_checked('user_erasure_request');
		return TRUE;
	}

	public function process_due($limit = 25)
	{
		$rows = $this->db->select('user_id')->from('user_erasure_request')
			->where('completed_at', NULL)->where('execute_after <=', date('Y-m-d H:i:s'))
			->order_by('execute_after')->limit(max(1, min(100, (int)$limit)))->get(FALSE);
		$reports = [];
		foreach ($rows as $row)
		{
			$user = $this->model2('user', (int)$row['user_id']);
			if (!$user()) continue;
			try
			{
				$reports[$user->id] = $this->anonymize($user);
			}
			catch (Throwable $e)
			{
				$reports[$user->id] = ['core' => 'error', 'error' => $e->getMessage()];
			}
		}
		return $reports;
	}

	public function anonymize($user, $ignore_schedule = FALSE)
	{
		$request = $this->erasure_request($user);
		if (!$request) throw new RuntimeException((string)$this->lang('No erasure request is registered.'));
		if (!empty($request['completed_at'])) return $request['result'] ?: [];
		if (!$ignore_schedule && strtotime($request['execute_after']) > time()) throw new RuntimeException((string)$this->lang('The cancellation period has not ended.'));
		$this->assert_erasure_allowed($user);

		$user_id = (int)$user->id;
		$files = $this->db->select('id', 'path')->from('file')->where('user_id', $user_id)->get(FALSE);
		$report = ['core' => 'included', 'modules' => [], 'file_warnings' => []];
		$this->db->begin_transaction();
		try
		{
			foreach (HB()->model2('addon')->get('module') as $module)
			{
				if (!$module->is_enabled() || $module->info()->name === 'user') continue;
				$name = $module->info()->name;
				$result = $module->personal_data_erase($user);
				$report['modules'][$name] = [
					'status' => $result === NULL ? 'not_implemented' : 'included',
					'data'   => $result
				];
			}

			$this->db->where('type', 'user')->where('entity', (string)$user_id)->delete_checked('access_details');
			foreach (['user_auth', 'user_field_value', 'users_groups', 'user_token', 'session', 'session_history', 'tracking', 'users_messages_recipients'] as $table)
			{
				$this->db->where('user_id', $user_id)->delete_checked($table);
			}
			$this->db->where('id', $user_id)->delete_checked('user_profile');
			$this->db->where('user_id', $user_id)->delete_checked('file');
			$this->db->where('id', $user_id)->update('user', [
				'username'           => (string)$this->lang('Deleted user'),
				'password'           => password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT),
				'email'              => NULL,
				'registration_date'  => date('Y-m-d H:i:s'),
				'last_activity_date' => NULL,
				'admin'              => FALSE,
				'language'           => NULL,
				'data'               => $this->storage->encode([]),
				'deleted'            => TRUE
			]);
			$this->db->where('user_id', $user_id)->update('user_erasure_request', [
				'completed_at' => date('Y-m-d H:i:s'),
				'result'       => $this->storage->encode($report)
			]);
			$this->db->commit();
		}
		catch (Throwable $e)
		{
			$this->db->rollback();
			throw $e;
		}

		foreach ($files as $file)
		{
			$path = (string)$file['path'];
			if (check_file($path) && !@unlink($path)) $report['file_warnings'][] = (int)$file['id'];
		}
		if ($report['file_warnings'])
		{
			$this->db->where('user_id', $user_id)->update('user_erasure_request', ['result' => $this->storage->encode($report)]);
		}

		return $report;
	}

	public function export($user)
	{
		$user_id = (int)$user->id;
		$profile = $this->db->from('user_profile')->where('id', $user_id)->row(FALSE);
		$custom_fields = $this->db
			->select('f.id', 'f.name', 'f.label', 'f.type', 'f.required', 'v.value')
			->from('user_field_value v')
			->join('user_field f', 'f.id = v.field_id', 'INNER')
			->where('v.user_id', $user_id)
			->order_by('f.id')
			->get(FALSE);

		foreach ($custom_fields as &$field)
		{
			$field['value'] = json_decode($field['value'], TRUE);
		}
		unset($field);

		$files = $this->db->select('id', 'name', 'path', 'date')
			->from('file')->where('user_id', $user_id)->order_by('date')->get(FALSE);
		$attachments = [];
		foreach ($files as &$file)
		{
			$source = str_replace('\\', '/', (string)$file['path']);
			$name = $this->archive_name($file['id'], $file['name']);
			$file['archive_file'] = check_file($source) ? 'files/'.$name : NULL;
			$file['available'] = $file['archive_file'] !== NULL;
			if ($file['available']) $attachments[] = [$source, $file['archive_file']];
			unset($file['path']);
		}
		unset($file);

		$data = [
			'export' => [
				'format'       => 'hiddencms-personal-data',
				'version'      => 1,
				'generated_at' => gmdate('c'),
				'site'         => (string)$this->config->name,
				'user_id'      => $user_id
			],
			'account' => $this->db
				->select('id', 'username', 'email', 'registration_date', 'last_activity_date', 'admin', 'language', 'data', 'deleted')
				->from('user')->where('id', $user_id)->row(FALSE),
			'profile' => $profile ?: [],
			'custom_fields' => $custom_fields,
			'groups' => $this->db
				->select('g.group_id', 'g.name', 'g.color', 'g.icon', 'g.hidden')
				->from('users_groups ug')->join('groups g', 'g.group_id = ug.group_id', 'INNER')
				->where('ug.user_id', $user_id)->order_by('g.order')->get(FALSE),
			'external_accounts' => $this->db
				->select('a.name as provider', 'ua.key as external_id', 'ua.username', 'ua.avatar')
				->from('user_auth ua')->join('addon a', 'a.id = ua.authenticator_id', 'INNER')
				->where('ua.user_id', $user_id)->order_by('ua.id')->get(FALSE),
			'active_sessions' => $this->db
				->select('remember', 'last_activity')->from('session')
				->where('user_id', $user_id)->order_by('last_activity')->get(FALSE),
			'connection_history' => $this->db
				->select('ip_address', 'host_name', 'referer', 'user_agent', 'auth', 'date')
				->from('session_history')->where('user_id', $user_id)->order_by('date')->get(FALSE),
			'permissions' => $this->db
				->select('a.module', 'a.action', 'a.id as resource_id', 'd.authorized')
				->from('access_details d')->join('access a', 'a.access_id = d.access_id', 'INNER')
				->where('d.type', 'user')->where('d.entity', (string)$user_id)->get(FALSE),
			'comments' => $this->db
				->select('id', 'parent_id', 'module', 'module_id', 'content', 'date')
				->from('comments')->where('user_id', $user_id)->order_by('date')->get(FALSE),
			'messages_written' => $this->db
				->select('r.reply_id', 'r.message_id', 'm.title', 'r.message', 'r.date')
				->from('users_messages_replies r')->join('users_messages m', 'm.message_id = r.message_id', 'INNER')
				->where('r.user_id', $user_id)->order_by('r.date')->get(FALSE),
			'mailbox' => $this->db
				->select('r.message_id', 'm.title', 'r.date as read_at', 'r.deleted')
				->from('users_messages_recipients r')->join('users_messages m', 'm.message_id = r.message_id', 'INNER')
				->where('r.user_id', $user_id)->order_by('r.message_id')->get(FALSE),
			'files' => $files,
			'tracking' => $this->db
				->select('model', 'model_id', 'date')->from('tracking')
				->where('user_id', $user_id)->order_by('date')->get(FALSE),
			'modules' => []
		];
		if (isset($data['account']['data']))
		{
			$data['account']['data'] = $this->storage->decode($data['account']['data'], []);
		}

		foreach (HB()->model2('addon')->get('module') as $module)
		{
			if (!$module->is_enabled() || $module->info()->name === 'user') continue;
			$name = $module->info()->name;
			try
			{
				$section = $module->personal_data_export($user);
				$data['modules'][$name] = [
					'status' => $section === NULL ? 'not_implemented' : 'included',
					'data'   => $section === NULL ? NULL : $section
				];
			}
			catch (Throwable $e)
			{
				$data['modules'][$name] = ['status' => 'error', 'data' => NULL];
			}
		}

		return ['data' => $data, 'attachments' => $attachments];
	}

	public function archive($user)
	{
		if (!class_exists(ZipArchive::class)) throw new RuntimeException((string)$this->lang('The PHP Zip extension is required to create the export.'));

		$export = $this->export($user);
		$file = tempnam(sys_get_temp_dir(), 'hiddencms-export-');
		if ($file === FALSE) throw new RuntimeException((string)$this->lang('Unable to create the temporary export file.'));

		$zip = new ZipArchive();
		try
		{
			if ($zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE)
			{
				throw new RuntimeException((string)$this->lang('Unable to create the export archive.'));
			}

			$json = json_encode($export['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
			$zip->addFromString('personal-data.json', $json."\n");
			foreach ($export['attachments'] as [$source, $name]) $zip->addFile($source, $name);
			if (!$zip->close()) throw new RuntimeException((string)$this->lang('Unable to finalize the export archive.'));
		}
		catch (Throwable $e)
		{
			$zip->close();
			@unlink($file);
			throw $e;
		}

		return $file;
	}

	private function archive_name($id, $name)
	{
		$name = preg_replace('/[^a-zA-Z0-9._-]+/', '-', basename((string)$name));
		$name = trim($name, '.-');
		return (int)$id.'-'.($name !== '' ? $name : 'file');
	}

	private function assert_erasure_allowed($user)
	{
		if (!$user || !$user()) throw new RuntimeException('Compte utilisateur introuvable.');
		if ($user->admin && $this->db->from('user')->where('admin', TRUE)->where('deleted', FALSE)->count() <= 1)
		{
			throw new RuntimeException((string)$this->lang('The last active administrator account cannot be deleted.'));
		}
	}
}
