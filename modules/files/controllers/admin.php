<?php
/**
 * https://neofr.ag
 * @author: HiddenCMS
 */

namespace HB\Modules\Files\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Admin extends Controller_Module
{
	private function root()
	{
		$root = HIDDENCMS_CMS.'/upload';
		dir_create($root);

		return str_replace('\\', '/', realpath($root));
	}

	private function normalize($path)
	{
		$path = trim(str_replace('\\', '/', (string)$path), '/');

		if ($path === '')
		{
			return '';
		}

		$parts = [];

		foreach (explode('/', $path) as $part)
		{
			$part = trim($part);

			if ($part === '' || $part === '.')
			{
				continue;
			}

			if ($part === '..')
			{
				return NULL;
			}

			$parts[] = $part;
		}

		return implode('/', $parts);
	}

	private function full_path($path)
	{
		$path = $this->normalize($path);

		if ($path === NULL)
		{
			return NULL;
		}

		$root = $this->root();
		$full = $root.($path !== '' ? '/'.$path : '');
		$check = file_exists($full) ? realpath($full) : realpath(dirname($full));

		if (!$check)
		{
			return NULL;
		}

		$check = str_replace('\\', '/', $check);

		if (stripos($check, $root) !== 0)
		{
			return NULL;
		}

		return $full;
	}

	private function clean_name($name)
	{
		$name = trim(str_replace(['/', '\\'], '', (string)$name));

		return in_array($name, ['', '.', '..'], TRUE) ? '' : $name;
	}

	private function current_dir()
	{
		$dir = $this->normalize(isset($_GET['dir']) ? $_GET['dir'] : '');

		if ($dir === NULL || !is_dir($this->full_path($dir)))
		{
			return '';
		}

		return $dir;
	}

	private function index_path($dir = '')
	{
		$dir = $this->normalize($dir);

		return 'admin/files'.($dir ? '?dir='.rawurlencode($dir) : '');
	}

	private function index_url($dir = '')
	{
		return url($this->index_path($dir));
	}

	private function public_url($file)
	{
		$file = $this->normalize($file);
		$parts = array_map('rawurlencode', explode('/', 'upload/'.$file));

		return url(implode('/', $parts));
	}

	private function unique_path($dir, $name)
	{
		$name = $this->clean_name($name);
		$path = $dir.($dir !== '' ? '/' : '').$name;
		$full = $this->full_path($path);

		if (!$full || !file_exists($full))
		{
			return [$path, $full];
		}

		$extension = pathinfo($name, PATHINFO_EXTENSION);
		$base = $extension ? substr($name, 0, -strlen($extension) - 1) : $name;
		$i = 2;

		do
		{
			$candidate = $base.'-'.$i.($extension ? '.'.$extension : '');
			$path = $dir.($dir !== '' ? '/' : '').$candidate;
			$full = $this->full_path($path);
			$i++;
		}
		while ($full && file_exists($full));

		return [$path, $full];
	}

	private function handle_post($dir)
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !post('files_action'))
		{
			return;
		}

		$action = post('files_action');

		if ($action === 'upload')
		{
			if (!$this->is_authorized('add_files'))
			{
				$this->error->unauthorized();
			}

			$count = 0;

			foreach ((array)@$_FILES['files']['name'] as $i => $name)
			{
				if (!empty($_FILES['files']['error'][$i]) || empty($_FILES['files']['tmp_name'][$i]))
				{
					continue;
				}

				$name = $this->clean_name($name);

				if ($name === '')
				{
					continue;
				}

				list($relative, $target) = $this->unique_path($dir, $name);

				if ($target && move_uploaded_file($_FILES['files']['tmp_name'][$i], $target))
				{
					$count++;
				}
			}

			notify($count ? $this->lang('%d fichier(s) ajoute(s)', $count) : $this->lang('Aucun fichier ajoute'), $count ? 'success' : 'warning');
			redirect($this->index_path($dir));
		}

		if ($action === 'mkdir')
		{
			if (!$this->is_authorized('add_files'))
			{
				$this->error->unauthorized();
			}

			$name = $this->clean_name(post('name'));

			if ($name === '')
			{
				notify($this->lang('Nom de dossier invalide'), 'danger');
				redirect($this->index_path($dir));
			}

			list($relative, $target) = $this->unique_path($dir, $name);
			dir_create($target);
			notify($this->lang('Dossier cree avec succes'));
			redirect($this->index_path($dir));
		}

		if ($action === 'delete')
		{
			if (!$this->is_authorized('delete_files'))
			{
				$this->error->unauthorized();
			}

			$path = $this->normalize(post('path'));
			$full = $path !== '' ? $this->full_path($path) : NULL;

			if (!$full || !file_exists($full))
			{
				notify($this->lang('Element introuvable'), 'danger');
				redirect($this->index_path($dir));
			}

			is_dir($full) ? dir_remove($full) : unlink($full);
			notify($this->lang('Element supprime avec succes'));
			redirect($this->index_path($dir));
		}

		if ($action === 'move')
		{
			if (!$this->is_authorized('modify_files'))
			{
				$this->error->unauthorized();
			}

			$source = $this->normalize(post('path'));
			$target_dir = $this->normalize(post('target_dir'));
			$name = $this->clean_name(post('name'));
			$source_full = $source !== '' ? $this->full_path($source) : NULL;

			if (!$source_full || !file_exists($source_full) || $target_dir === NULL || !is_dir($this->full_path($target_dir)) || $name === '')
			{
				notify($this->lang('Deplacement impossible'), 'danger');
				redirect($this->index_path($dir));
			}

			$target = $target_dir.($target_dir !== '' ? '/' : '').$name;

			if (is_dir($source_full) && ($target === $source || strpos($target.'/', $source.'/') === 0))
			{
				notify($this->lang('Impossible de deplacer un dossier dans lui-meme'), 'danger');
				redirect($this->index_path($dir));
			}

			$target_full = $this->full_path($target);

			if (!$target_full || file_exists($target_full))
			{
				notify($this->lang('Un element existe deja a cet emplacement'), 'danger');
				redirect($this->index_path($dir));
			}

			rename($source_full, $target_full);
			notify($this->lang('Element deplace avec succes'));
			redirect($this->index_path($target_dir));
		}
	}

	private function directories($base = '')
	{
		$base = $this->normalize($base);
		$full = $this->full_path($base);
		$dirs = ['' => '/'];

		if (!$full || !is_dir($full))
		{
			return $dirs;
		}

		$scan = function($dir, $relative) use (&$scan, &$dirs){
			foreach (scandir($dir) as $entry)
			{
				if (in_array($entry, ['.', '..'], TRUE) || !is_dir($dir.'/'.$entry))
				{
					continue;
				}

				$path = $relative.($relative !== '' ? '/' : '').$entry;
				$dirs[$path] = $path;
				$scan($dir.'/'.$entry, $path);
			}
		};

		$scan($this->root(), '');

		return $dirs;
	}

	private function render_tree($current)
	{
		$directories = $this->directories();
		$html = '<div class="files-tree-heading">'.icon('far fa-folder-open').' '.$this->lang('Dossiers').'</div><ul class="files-tree">';

		foreach ($directories as $path => $label)
		{
			$depth = $path === '' ? 0 : substr_count($path, '/') + 1;
			$title = $path === '' ? $this->lang('Racine') : basename($path);

			$html .= '<li style="padding-left: '.($depth * 14).'px">'
					.'<a class="'.($path === $current ? 'active' : '').'" href="'.$this->index_url($path).'">'
						.icon('far fa-folder').' '.utf8_htmlentities($title)
					.'</a>'
				.'</li>';
		}

		return $html.'</ul>';
	}

	private function render_breadcrumb($dir)
	{
		$html = '<nav class="files-breadcrumb">'.icon('fas fa-map-marker-alt').'<a href="'.$this->index_url().'">'.$this->lang('Racine').'</a>';
		$path = '';

		foreach (array_filter(explode('/', $dir)) as $part)
		{
			$path .= ($path !== '' ? '/' : '').$part;
			$html .= '<span>/</span><a href="'.$this->index_url($path).'">'.utf8_htmlentities($part).'</a>';
		}

		return $html.'</nav>';
	}

	private function render_select_dirs($selected = '')
	{
		$html = '<select class="form-control form-control-sm" name="target_dir">';

		foreach ($this->directories() as $path => $label)
		{
			$html .= '<option value="'.utf8_htmlentities($path).'"'.($path === $selected ? ' selected' : '').'>'.utf8_htmlentities($label).'</option>';
		}

		return $html.'</select>';
	}

	private function render_items($dir)
	{
		$full = $this->full_path($dir);
		$items = [];

		foreach (scandir($full) as $entry)
		{
			if (in_array($entry, ['.', '..'], TRUE))
			{
				continue;
			}

			$path = $dir.($dir !== '' ? '/' : '').$entry;
			$item_full = $this->full_path($path);

			$items[] = [
				'name' => $entry,
				'path' => $path,
				'dir'  => is_dir($item_full),
				'size' => is_file($item_full) ? filesize($item_full) : NULL,
				'date' => filemtime($item_full)
			];
		}

		usort($items, function($a, $b){
			if ($a['dir'] !== $b['dir'])
			{
				return $a['dir'] ? -1 : 1;
			}

			return strnatcasecmp($a['name'], $b['name']);
		});

		if (empty($items))
		{
			return '<div class="files-empty">'.icon('far fa-folder-open').'<span>'.$this->lang('Ce dossier est vide').'</span></div>';
		}

		$rows = '';

		foreach ($items as $item)
		{
			$name = utf8_htmlentities($item['name']);
			$icon = $item['dir'] ? 'far fa-folder' : 'far fa-file';
			$title = $item['dir'] ? '<a href="'.$this->index_url($item['path']).'">'.$name.'</a>' : '<a href="'.$this->public_url($item['path']).'" target="_blank" rel="noopener">'.$name.'</a>';
			$size = $item['dir'] ? '-' : human_size($item['size']);
			$date = date('d/m/Y H:i', $item['date']);
			$move = '';
			$delete = '';

			if ($this->is_authorized('modify_files'))
			{
				$move = '<form class="files-inline-form" method="post" action="'.$this->index_url($dir).'">'
						.'<input type="hidden" name="files_action" value="move">'
						.'<input type="hidden" name="path" value="'.utf8_htmlentities($item['path']).'">'
						.'<input class="form-control form-control-sm" type="text" name="name" value="'.$name.'">'
						.$this->render_select_dirs($dir)
						.'<button class="btn btn-outline-primary btn-sm" type="submit">'.icon('fas fa-exchange-alt').'</button>'
					.'</form>';
			}

			if ($this->is_authorized('delete_files'))
			{
				$delete = '<form method="post" action="'.$this->index_url($dir).'" onsubmit="return confirm(\''.$this->lang('Supprimer cet element ?').'\');">'
						.'<input type="hidden" name="files_action" value="delete">'
						.'<input type="hidden" name="path" value="'.utf8_htmlentities($item['path']).'">'
						.'<button class="btn btn-outline-danger btn-sm" type="submit">'.icon('far fa-trash-alt').'</button>'
					.'</form>';
			}

			$rows .= '<tr>'
					.'<td class="files-name">'.icon($icon).' '.$title.'</td>'
					.'<td class="files-size">'.$size.'</td>'
					.'<td class="files-date">'.$date.'</td>'
					.'<td class="files-actions">'.$move.$delete.'</td>'
				.'</tr>';
		}

		return '<div class="table-responsive">'
				.'<table class="table table-hover table-sm files-table mb-0">'
					.'<thead><tr>'
						.'<th>'.$this->lang('Nom').'</th>'
						.'<th>'.$this->lang('Taille').'</th>'
						.'<th>'.$this->lang('Date').'</th>'
						.'<th class="text-right">'.$this->lang('Actions').'</th>'
					.'</tr></thead>'
					.'<tbody>'.$rows.'</tbody>'
				.'</table>'
			.'</div>';
	}

	private function render_toolbar($dir)
	{
		$upload = '';
		$mkdir = '';

		if ($this->is_authorized('add_files'))
		{
			$upload = '<form class="files-toolbar-form" method="post" action="'.$this->index_url($dir).'" enctype="multipart/form-data">'
					.'<input type="hidden" name="files_action" value="upload">'
					.'<label>'.$this->lang('Ajouter des fichiers').'</label>'
					.'<div class="files-toolbar-control">'
						.'<input class="form-control form-control-sm" type="file" name="files[]" multiple>'
						.'<button class="btn btn-primary btn-sm" type="submit">'.icon('fas fa-upload').' '.$this->lang('Ajouter').'</button>'
					.'</div>'
				.'</form>';

			$mkdir = '<form class="files-toolbar-form" method="post" action="'.$this->index_url($dir).'">'
					.'<input type="hidden" name="files_action" value="mkdir">'
					.'<label>'.$this->lang('Nouveau dossier').'</label>'
					.'<div class="files-toolbar-control">'
						.'<input class="form-control form-control-sm" type="text" name="name" placeholder="'.$this->lang('Nom du dossier').'">'
						.'<button class="btn btn-outline-primary btn-sm" type="submit">'.icon('fas fa-folder-plus').' '.$this->lang('Creer').'</button>'
					.'</div>'
				.'</form>';
		}

		return '<div class="files-toolbar">'.$upload.$mkdir.'</div>';
	}

	public function index()
	{
		$this->title($this->lang('Fichiers'));
		$this->css('file_manager');

		$dir = $this->current_dir();
		$this->handle_post($dir);

		$content = '<div class="row files-manager">'
				.'<div class="col-md-3 files-sidebar"><div class="files-sidebar-card">'.$this->render_tree($dir).'</div></div>'
				.'<div class="col-md-9 files-content">'
					.$this->render_breadcrumb($dir)
					.$this->render_toolbar($dir)
					.$this->render_items($dir)
				.'</div>'
			.'</div>';

		return $this->panel()
					->heading($this->lang('Gestionnaire de fichiers'), 'far fa-folder-open')
					->body($content, FALSE);
	}
}
