<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Displayables;

use HB\HiddenCMS\Displayable;

class Row extends Displayable
{
	protected $_style;

	public function __sleep()
	{
		return array_merge(parent::__sleep(), ['_style']);
	}

	public function style($style = NULL)
	{
		if (func_num_args())
		{
			$this->_style = $style;
			return $this;
		}

		return $this->_style;
	}

	public function __toString()
	{
		$output = '';

		$live_editor = FAlSE;

		if ($this->_id !== NULL)
		{
			foreach ($this->_array as $i => $child)
			{
				$child->id($i);
			}

			if ($live_editor = HB()->output->live_editor() & \HB\HiddenCMS\Core\Output::ROWS)
			{
				$output .= '<div class="live-editor-row-header">
								<div class="btn-group">
									<button type="button" class="btn btn-sm btn-info live-editor-style" data-toggle="tooltip" data-container="body" title="'.HB()->lang('Apparence').'">'.icon('fas fa-paint-brush').'</button>
									<button type="button" class="btn btn-sm btn-danger live-editor-delete" data-toggle="tooltip" data-container="body" title="'.HB()->lang('Supprimer').'">'.icon('fas fa-times').'</button>
								</div>
								<h3>'.HB()->lang('Row').' <div class="btn-group"><button type="button" class="btn btn-xs btn-success live-editor-add-col" data-toggle="tooltip" data-container="body" title="'.HB()->lang('Nouveau Col').'">'.icon('fas fa-plus').'</button></div></h3>
							</div>';
			}
		}

		$output .= '<div class="row'.(!empty($this->_style) ? ' '.$this->_style.($live_editor ? '" data-original-style="'.$this->_style : '') : '').'"'.($this->_id !== NULL ? ' data-row-id="'.$this->_id.'"' : '').'>
						'.parent::__toString().'
					</div>';

		return $live_editor ? '<div class="live-editor-row">'.$output.'</div>' : $output;
	}
}


