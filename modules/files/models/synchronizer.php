<?php

namespace HB\Modules\Files\Models;

use FilesystemIterator;
use HB\HiddenCMS\Loadables\Model;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Throwable;

class Synchronizer extends Model
{
	public function sync($dry_run = FALSE)
	{
		$root = HIDDENCMS_CMS.'/upload/files';
		dir_create($root);
		$root = str_replace('\\', '/', realpath($root));

		if (!$root || !is_dir($root) || !is_readable($root))
		{
			throw new RuntimeException('The media directory is not readable.');
		}

		$directories = [''];
		$files = [];
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ($iterator as $item)
		{
			if ($item->isLink())
			{
				continue;
			}

			$path = str_replace('\\', '/', $item->getPathname());
			$relative = ltrim(substr($path, strlen($root)), '/');

			if ($relative === '')
			{
				continue;
			}

			if ($item->isDir())
			{
				$directories[] = $relative;
			}
			else if ($item->isFile())
			{
				$files[] = [
					'relative' => $relative,
					'path'     => 'upload/files/'.$relative,
					'name'     => $item->getFilename(),
					'date'     => date('Y-m-d H:i:s', $item->getMTime())
				];
			}
		}

		usort($directories, function($a, $b){
			return substr_count($a, '/') <=> substr_count($b, '/') ?: strcmp($a, $b);
		});

		$known_directories = [];
		foreach ($this->db->select('directory_id', 'path')->from('files_directories')->get(FALSE) as $directory)
		{
			$known_directories[$this->normalize($directory['path'])] = (int)$directory['directory_id'];
		}

		$known_files = [];
		foreach ($this->db->select('id', 'path')->from('file')->where('path LIKE', 'upload/files/%')->get(FALSE) as $file)
		{
			$known_files[$this->normalize($file['path'])] = (int)$file['id'];
		}

		$report = ['directories' => 0, 'files' => 0, 'renamed' => 0, 'permissions' => 0, 'skipped' => 0];

		if ($dry_run)
		{
			foreach ($directories as $directory)
			{
				if (isset($known_directories[$directory]))
				{
					if ($directory !== '' && !$this->has_access_details('read_directory', $known_directories[$directory]))
					{
						$report['permissions']++;
					}
				}
				else
				{
					if (strlen($directory) <= 255)
					{
						$report['directories']++;
					}
					else
					{
						$report['skipped']++;
					}
				}
			}

			foreach ($files as $file)
			{
				$db_path = $this->normalize($file['path']);

				if (isset($known_files[$db_path]))
				{
					if (!$this->has_storage_name($db_path))
					{
						$report['renamed']++;
					}

					if (!$this->has_access_details('read_file', $known_files[$db_path]))
					{
						$report['permissions']++;
					}
				}
				else
				{
					if (strlen($db_path) <= 255 && strlen($file['name']) <= 255)
					{
						$report['files']++;
					}
					else
					{
						$report['skipped']++;
					}
				}
			}

			return $report;
		}

		$moves = [];
		$this->db->begin_transaction();

		try
		{
			foreach ($directories as $directory)
			{
				if (isset($known_directories[$directory]))
				{
					$id = $known_directories[$directory];

					if ($directory !== '' && !$this->has_access_details('read_directory', $id))
					{
						$this->inherit_access('read_directory', $this->parent_directory_id($directory, $known_directories), 'read_directory', $id, 'directory');
						$report['permissions']++;
					}

					continue;
				}

				if (strlen($directory) > 255)
				{
					$report['skipped']++;
					continue;
				}

				$id = (int)$this->db->insert_checked('files_directories', ['path' => $directory]);
				$known_directories[$directory] = $id;

				if ($directory === '')
				{
					$this->ensure_access('read_directory', $id, 'directory');
				}
				else
				{
					$this->inherit_access('read_directory', $this->parent_directory_id($directory, $known_directories), 'read_directory', $id, 'directory');
				}

				$report['directories']++;
			}

			foreach ($files as $file)
			{
				$db_path = $this->normalize($file['path']);

				if (isset($known_files[$db_path]))
				{
					$id = $known_files[$db_path];

					if (!$this->has_storage_name($db_path))
					{
						$stored_path = $this->move_to_storage($db_path, $moves);
						$this->db->where('id', $id)->update('file', ['path' => $stored_path]);
						$report['renamed']++;
					}

					if (!$this->has_access_details('read_file', $id))
					{
						$directory = dirname($file['relative']);
						$directory = $directory === '.' ? '' : $this->normalize($directory);
						$this->inherit_access('read_directory', $known_directories[$directory], 'read_file', $id, 'file');
						$report['permissions']++;
					}

					continue;
				}

				if (strlen($db_path) > 255 || strlen($file['name']) > 255)
				{
					$report['skipped']++;
					continue;
				}

				$stored_path = $this->move_to_storage($db_path, $moves);
				$id = (int)$this->db->insert_checked('file', [
					'user_id' => NULL,
					'name'    => $file['name'],
					'path'    => $stored_path,
					'date'    => $file['date']
				]);
				$known_files[$stored_path] = $id;
				$directory = dirname($file['relative']);
				$directory = $directory === '.' ? '' : $this->normalize($directory);
				$this->inherit_access('read_directory', $known_directories[$directory], 'read_file', $id, 'file');
				$report['files']++;
			}

			$this->db->commit();
			$this->access->reload();
		}
		catch (Throwable $e)
		{
			$this->db->rollback();

			foreach (array_reverse($moves) as $move)
			{
				if (is_file($move['to']) && !file_exists($move['from']))
				{
					@rename($move['to'], $move['from']);
				}
			}

			throw $e;
		}

		return $report;
	}

	private function normalize($path)
	{
		return trim(str_replace('\\', '/', (string)$path), '/');
	}

	private function has_storage_name($path)
	{
		return (bool)preg_match('/^[a-z0-9]{32}(?:\.[a-z0-9]+)?$/', basename($path));
	}

	private function move_to_storage($db_path, array &$moves)
	{
		$source = HIDDENCMS_CMS.'/'.$db_path;

		if (!is_file($source))
		{
			throw new RuntimeException('Unable to find the file to import: '.$db_path);
		}

		$directory = dirname($db_path);
		$extension = strtolower(pathinfo($db_path, PATHINFO_EXTENSION));

		do
		{
			$stored_path = $this->normalize($directory.'/'.unique_id().($extension !== '' ? '.'.$extension : ''));
			$target = HIDDENCMS_CMS.'/'.$stored_path;
		}
		while (file_exists($target));

		if (!@rename($source, $target))
		{
			throw new RuntimeException('Unable to rename the imported file: '.$db_path);
		}

		$moves[] = ['from' => $source, 'to' => $target];

		return $stored_path;
	}

	private function parent_directory_id($directory, array $known)
	{
		$parent = dirname($directory);
		$parent = $parent === '.' ? '' : $this->normalize($parent);

		return $known[$parent];
	}

	private function inherit_access($source_action, $source_id, $target_action, $target_id, $target_type)
	{
		$this->ensure_access($target_action, $target_id, $target_type);
		$source = $this->access_id($source_action, $source_id);
		$target = $this->access_id($target_action, $target_id);
		$permissions = $source
			? $this->db->select('entity', 'type', 'authorized')->from('access_details')->where('access_id', $source)->get(FALSE)
			: [];

		$this->db->where('access_id', $target)->delete_checked('access_details');

		if (!$permissions)
		{
			$permissions[] = [
				'entity'     => 'visitors',
				'type'       => 'group',
				'authorized' => TRUE
			];
		}

		foreach ($permissions as $permission)
		{
			$this->db->insert_checked('access_details', [
				'access_id'  => $target,
				'entity'     => $permission['entity'],
				'type'       => $permission['type'],
				'authorized' => $permission['authorized']
			]);
		}
	}

	private function ensure_access($action, $id, $type)
	{
		if (!$this->access_id($action, $id))
		{
			$this->access->init('files', $type, $id);
		}
	}

	private function has_access_details($action, $id)
	{
		$access_id = $this->access_id($action, $id);

		return $access_id && (bool)$this->db->select('access_id')->from('access_details')->where('access_id', $access_id)->row();
	}

	private function access_id($action, $id)
	{
		return (int)$this->db->select('access_id')->from('access')->where('module', 'files')->where('action', $action)->where('id', $id)->row();
	}
}
