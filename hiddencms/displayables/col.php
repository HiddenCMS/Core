<?php
/**
 * https://neofr.ag
 * @author: Micha?l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Displayables;

use HB\HiddenCMS\Displayable;

class Col extends Displayable
{
	protected $_size;

	public function __sleep()
	{
		return array_merge(parent::__sleep(), ['_size']);
	}

	public function size($size = NULL)
	{
		if (func_num_args())
		{
			$this->_size = $size;
			return $this;
		}

		return $this->_size;
	}

	private function admin_grid()
	{
		return ($theme = HB()->output->theme()) && $theme->info()->name == 'admin';
	}

	private function semantic_width($size)
	{
		$size = max(1, min(12, (int)$size));
		$width = max(1, min(16, (int)round($size * 16 / 12)));
		$words = [
			1 => 'one',
			2 => 'two',
			3 => 'three',
			4 => 'four',
			5 => 'five',
			6 => 'six',
			7 => 'seven',
			8 => 'eight',
			9 => 'nine',
			10 => 'ten',
			11 => 'eleven',
			12 => 'twelve',
			13 => 'thirteen',
			14 => 'fourteen',
			15 => 'fifteen',
			16 => 'sixteen'
		];

		return $words[$width];
	}

	private function semantic_device($breakpoint)
	{
		$devices = [
			'sm' => 'tablet',
			'md' => 'tablet',
			'lg' => 'computer',
			'xl' => 'large screen'
		];

		return isset($devices[$breakpoint]) ? $devices[$breakpoint] : NULL;
	}

	private function semantic_size($size)
	{
		$tokens = preg_split('/\s+/', trim((string)$size));
		$classes = [];
		$base = NULL;
		$responsive = [];

		foreach ($tokens as $token)
		{
			if ($token === '')
			{
				continue;
			}

			if (preg_match('/^col-(\d+)$/', $token, $match))
			{
				$base = $this->semantic_width($match[1]);
				continue;
			}

			if (preg_match('/^col-(sm|md|lg|xl)-(\d+)$/', $token, $match))
			{
				if ($device = $this->semantic_device($match[1]))
				{
					$responsive[] = $this->semantic_width($match[2]).' wide '.$device;
				}
				continue;
			}

			if ($token === 'col')
			{
				continue;
			}

			$classes[] = $token;
		}

		if ($responsive)
		{
			$classes[] = ($base ?: 'sixteen').' wide mobile';
			$classes = array_merge($classes, $responsive);
		}
		else
		{
			$classes[] = ($base ?: 'sixteen').' wide';
		}

		$classes[] = 'column';

		return implode(' ', array_unique($classes));
	}

	public function __toString()
	{
		$size = $this->_size;

		foreach ($this as $i => $child)
		{
			if (method_exists($child, 'size') && !$size)
			{
				$size = $child->size();
			}
		}

		if (!$this->admin_grid() && !is_null($size) && preg_match('/^col-(\d+)$/', $size, $match) && (int)$match[1] < 12)
		{
			$size = 'col-12 col-lg-'.$match[1];
		}

		if ($this->_id !== NULL)
		{
			foreach ($this as $i => $child)
			{
				$child->id($i);
			}
		}

		$output = parent::__toString();

		if ($this->_id !== NULL && HB()->output->live_editor() & \HB\HiddenCMS\Core\Output::COLS)
		{
			$output = '<div class="live-editor-col">
							<div class="btn-group">
								<button type="button" class="btn btn-sm btn-light live-editor-size" data-size="-1" data-toggle="tooltip" data-container="body" title="'.HB()->lang('Réduire').'">'.icon('fas fa-compress fa-rotate-45').'</button>
								<button type="button" class="btn btn-sm btn-light live-editor-size" data-size="1" data-toggle="tooltip" data-container="body" title="'.HB()->lang('Augmenter').'">'.icon('fas fa-expand fa-rotate-45').'</button>
								<button type="button" class="btn btn-sm btn-danger live-editor-delete" data-toggle="tooltip" data-container="body" title="'.HB()->lang('Supprimer').'">'.icon('fas fa-times').'</button>
							</div>
							<h3>'.HB()->lang('Col').' <div class="btn-group"><button type="button" class="btn btn-xs btn-success live-editor-add-widget" data-toggle="tooltip" data-container="body" title="'.HB()->lang('Nouveau Widget').'">'.icon('fas fa-plus').'</button></div></h3>
							'.$output.'
						</div>';
		}

		$class = $this->admin_grid() ? $this->semantic_size($size ?: 'col-12') : ($size ?: 'col-12');

		return '<div class="'.$class.'"'.($this->_id !== NULL ? ' data-col-id="'.$this->_id.'"' : '').'>'.$output.'</div>';
	}
}

