<?php

namespace HB\Modules\Settings\Models;

use HB\HiddenCMS\Loadables\Model;
use RuntimeException;
use Throwable;

class Retention extends Model
{
	const POLICIES = [
		'privacy_retention_connection_history' => [0, 30, 90, 180, 365],
		'privacy_retention_sessions'           => [0, 30, 90, 180, 365],
		'privacy_retention_db_logs'            => [0, 30, 90, 180, 365],
		'privacy_retention_erasure_reports'    => [0, 30, 90, 180, 365],
		'privacy_retention_backups'            => [0, 30, 90, 180, 365],
		'privacy_retention_log_files'          => [0, 7, 30, 90, 180],
		'privacy_retention_inactive_accounts'  => [0, 365, 730, 1095]
	];

	const LABELS = [
		'connection_history' => 'Connection history',
		'sessions'           => 'Sessions',
		'db_logs'            => 'Change log',
		'erasure_reports'    => 'Erasure reports',
		'backups'            => 'Backups',
		'log_files'          => 'Log files',
		'inactive_accounts'  => 'Inactive accounts to review'
	];

	public function values()
	{
		$values = [];
		foreach (self::POLICIES as $name => $allowed)
		{
			$value = (int)($this->config->$name ?? 0);
			$values[$name] = in_array($value, $allowed, TRUE) ? $value : 0;
		}
		return $values;
	}

	public function preview()
	{
		return $this->build_report();
	}

	public function run($execute = FALSE)
	{
		$report = $this->build_report();
		$report['mode'] = $execute ? 'purge' : 'simulation';
		$report['deleted'] = [];
		$report['warnings'] = [];

		if ($execute)
		{
			$this->db->begin_transaction();
			try
			{
				foreach ($report['rules'] as $key => $rule)
				{
					if (!$rule['enabled'] || !isset($rule['table'])) continue;
					$query = $this->db->where($rule['date'].' <', $rule['cutoff']);
					if (!empty($rule['where']))
					{
						foreach ($rule['where'] as $field => $value) $query->where($field, $value);
					}
					$report['deleted'][$key] = $query->delete_checked($rule['table']);
				}
				$this->db->commit();
			}
			catch (Throwable $e)
			{
				$this->db->rollback();
				$this->record('purge', 'failed', $report + ['error' => $e->getMessage()]);
				throw $e;
			}

			foreach ($report['filesystem']['backups'] as $backup)
			{
				if ($this->remove_directory($backup['path']))
				{
					$report['deleted']['backups'] = ($report['deleted']['backups'] ?? 0) + 1;
				}
				else
				{
					$report['warnings'][] = (string)$this->lang('Backup not deleted: %s', $backup['id']);
				}
			}
			foreach ($report['filesystem']['logs'] as $log)
			{
				if (@unlink($log['path']))
				{
					$report['deleted']['log_files'] = ($report['deleted']['log_files'] ?? 0) + 1;
				}
				else
				{
					$report['warnings'][] = (string)$this->lang('Log file not deleted: %s', $log['name']);
				}
			}
		}

		unset($report['filesystem']);
		$this->record($report['mode'], 'completed', $report);
		return $report;
	}

	public function last_run()
	{
		$row = $this->db->from('privacy_retention_run')->order_by('id DESC')->row(FALSE);
		if (!empty($row['report'])) $row['report'] = $this->storage->decode($row['report'], []);
		return $row ?: NULL;
	}

	public function summary(array $report)
	{
		$items = [];
		foreach ($report['candidates'] ?? [] as $key => $count)
		{
			$items[] = '<div class="item"><div class="content"><strong>'.utf8_htmlentities((string)$this->lang(self::LABELS[$key] ?? $key)).'</strong><div class="description">'.$this->lang('%d items', (int)$count).'</div></div></div>';
		}
		return '<div class="ui relaxed divided list">'.implode('', $items).'</div>';
	}

	private function build_report()
	{
		$values = $this->values();
		$rules = [
			'connection_history' => $this->rule($values, 'privacy_retention_connection_history', 'session_history', 'date'),
			'sessions'           => $this->rule($values, 'privacy_retention_sessions', 'session', 'last_activity'),
			'db_logs'            => $this->rule($values, 'privacy_retention_db_logs', 'log_db', 'date'),
			'erasure_reports'    => $this->rule($values, 'privacy_retention_erasure_reports', 'user_erasure_request', 'completed_at', ['completed_at <>' => NULL])
		];
		$candidates = [];
		foreach ($rules as $key => &$rule)
		{
			$rule['count'] = 0;
			if ($rule['enabled'])
			{
				$query = $this->db->from($rule['table'])->where($rule['date'].' <', $rule['cutoff']);
				foreach ($rule['where'] as $field => $value) $query->where($field, $value);
				$rule['count'] = (int)$query->count();
			}
			$candidates[$key] = $rule['count'];
		}
		unset($rule);

		$backups = $this->backup_candidates($values['privacy_retention_backups']);
		$logs = $this->log_candidates($values['privacy_retention_log_files']);
		$candidates['backups'] = count($backups);
		$candidates['log_files'] = count($logs);
		$candidates['inactive_accounts'] = $this->inactive_accounts($values['privacy_retention_inactive_accounts']);

		return [
			'generated_at' => date('c'),
			'policy'       => $values,
			'rules'        => $rules,
			'candidates'   => $candidates,
			'filesystem'   => ['backups' => $backups, 'logs' => $logs]
		];
	}

	private function rule(array $values, $setting, $table, $date, array $where = [])
	{
		$days = $values[$setting];
		return [
			'enabled' => $days > 0,
			'days'    => $days,
			'cutoff'  => $days ? date('Y-m-d H:i:s', strtotime('-'.$days.' days')) : NULL,
			'table'   => $table,
			'date'    => $date,
			'where'   => $where
		];
	}

	private function inactive_accounts($days)
	{
		if (!$days) return 0;
		$cutoff = date('Y-m-d H:i:s', strtotime('-'.$days.' days'));
		return (int)$this->db->query("SELECT COUNT(*) FROM user WHERE deleted = '0' AND admin = '0' AND COALESCE(last_activity_date, registration_date) < '".$this->db->escape_string($cutoff)."'")->row();
	}

	private function backup_candidates($days)
	{
		if (!$days) return [];
		$backups = array_values($this->core_updater->backups());
		$candidates = [];
		foreach ($backups as $index => $backup)
		{
			if ($index < 3 || empty($backup['id']) || empty($backup['created_at']) || strtotime($backup['created_at']) >= strtotime('-'.$days.' days')) continue;
			if (!preg_match('/^[a-z0-9-]+$/i', $backup['id'])) continue;
			$candidates[] = ['id' => $backup['id'], 'path' => HIDDENCMS_CMS.'/backups/updates/'.$backup['id']];
		}
		return $candidates;
	}

	private function log_candidates($days)
	{
		if (!$days) return [];
		$cutoff = strtotime('-'.$days.' days');
		$logs = [];
		foreach (glob(HIDDENCMS_CMS.'/logs/*') ?: [] as $file)
		{
			if (!is_file($file) || filemtime($file) >= $cutoff) continue;
			$logs[] = ['name' => basename($file), 'path' => $file];
		}
		return $logs;
	}

	private function record($mode, $status, array $report)
	{
		unset($report['filesystem']);
		$this->db->insert_checked('privacy_retention_run', [
			'mode'         => $mode,
			'status'       => $status,
			'started_at'   => date('Y-m-d H:i:s', strtotime($report['generated_at'] ?? 'now')),
			'completed_at' => date('Y-m-d H:i:s'),
			'report'       => $this->storage->encode($report)
		]);
	}

	private function remove_directory($directory)
	{
		$root = realpath(HIDDENCMS_CMS.'/backups/updates');
		$target = realpath($directory);
		if (!$root || !$target || dirname($target) !== $root) return FALSE;
		$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($target, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
		foreach ($iterator as $item)
		{
			if ($item->isDir()) @rmdir($item->getPathname());
			else @unlink($item->getPathname());
		}
		return @rmdir($target);
	}
}
