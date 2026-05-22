<?php
/**
 * https://neofr.ag
 * @author: HiddenCMS
 */

namespace HB\Modules\Files\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Admin extends Controller_Module
{
	private function resolve_filegator_root()
	{
		$root = rtrim(HIDDENCMS_CMS, '/\\');

		$candidates = [
			[
				'path'      => $root.'/tools/filegator/index.php',
				'entry_url' => '/tools/filegator/index.php'
			],
			[
				'path'      => $root.'/tools/index.php',
				'entry_url' => '/tools/index.php'
			]
		];

		foreach ($candidates as $candidate)
		{
			if (file_exists($candidate['path']))
			{
				return $candidate;
			}
		}

		return null;
	}

	private function app_root_url()
	{
		$root = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
		$root = ($root === '/' || $root === '.') ? '' : rtrim($root, '/');

		return $root;
	}

	private function filegator_url($filegator_entry_url)
	{
		return $this->app_root_url().$filegator_entry_url;
	}

	public function index()
	{
		$this->title($this->lang('Fichiers'));
		$this->css('file_manager');

		$filegator = $this->resolve_filegator_root();

		if ($filegator === null)
		{
			return $this->panel()
						->heading($this->lang('FileGator introuvable'), 'fas fa-exclamation-triangle')
						->body('<div class="table-empty">'.$this->lang('Le dossier tools/filegator (ou tools/) est introuvable').'</div>', FALSE);
		}

		$open_button = $this->button('', 'fas fa-external-link-alt')
							->class('hb-btn hb-btn-secondary hb-btn-icon')
							->tooltip($this->lang('Ouvrir dans un nouvel onglet'))
							->url($this->filegator_url($filegator['entry_url']))
							->attr('target', '_blank')
							->attr('rel', 'noopener');

		$content = '<div class="hb-files-embed-actions">'.$open_button.'</div>'
					.'<iframe class="hb-files-embed-iframe" src="'.$this->filegator_url($filegator['entry_url']).'" loading="lazy"></iframe>';

		return $this->panel()
					->style('hb-files-panel')
					->heading($this->lang('Gestionnaire de fichiers'), 'far fa-folder-open')
					->body($content, FALSE);
	}
}
