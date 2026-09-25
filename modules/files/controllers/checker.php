<?php
/**
 * https://neofr.ag
 * @author: HiddenCMS
 */

namespace HB\Modules\Files\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module_Checker;

class Checker extends Module_Checker
{
	private function normalize_db_path($path)
	{
		$path = trim(str_replace('\\', '/', (string)$path));

		if (strpos($path, './') === 0)
		{
			$path = substr($path, 2);
		}

		return trim($path, '/');
	}

	public function _file($slug)
	{
		$slug = trim((string)$slug);

		if ($slug === '')
		{
			return;
		}

		if (preg_match('/^([1-9][0-9]*)-(.+)$/', $slug, $matches))
		{
			$row = $this->db->select('id', 'name', 'path')->from('file')->where('id', (int)$matches[1])->row();

			if (is_array($row) && $this->public_slug($row) === $slug)
			{
				return $this->allowed_file($row);
			}

			return;
		}

		foreach ($this->db->select('id', 'name', 'path')->from('file')->get(FALSE) as $row)
		{
			$path = $this->normalize_db_path($row['path']);

			if (strpos($path, 'upload/files/') !== 0)
			{
				continue;
			}

			if (pathinfo(basename($path), PATHINFO_FILENAME) === $slug)
			{
				return $this->allowed_file($row);
			}
		}
	}

	private function public_slug(array $row)
	{
		$name = pathinfo((string)$row['name'], PATHINFO_FILENAME);
		$slug = url_title($name);

		return (int)$row['id'].'-'.($slug ?: 'file');
	}

	private function allowed_file(array $row)
	{
		$path = $this->normalize_db_path($row['path']);

		if (strpos($path, 'upload/files/') !== 0 || !$this->access('files', 'read_file', (int)$row['id']))
		{
			return;
		}

		if (($file = HB()->model2('file', (int)$row['id'])) && $file())
		{
			return [$file];
		}
	}
}
