<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Displayables;

use HB\HiddenCMS\Displayable;

class Zone extends Displayable
{
	public function display($disposition)
	{
		$output = HB()->disposition->decode($disposition['disposition']);

		if ($live_editor = HB()->output->live_editor())
		{
			$i = 0;

			$output->each(function($row) use (&$i){
				return $row->id($i++);
			});

			if ($live_editor & \HB\HiddenCMS\Core\Output::ZONES)
			{
				$zone_id = $disposition['zone'];
				$theme   = $this->theme($disposition['theme']);
				$fork    = '';

				if (strpos($disposition['page'], 'outline:') !== 0)
				{
					$fork = $disposition['page'] == '*'
						? '<button type="button" class="btn btn-link live-editor-fork" data-enabled="0">'.icon('fas fa-toggle-off').' '.HB()->lang('Common layout').'</button>'
						: '<button type="button" class="btn btn-link live-editor-fork" data-enabled="1">'.icon('fas fa-toggle-on').' '.HB()->lang('Page-specific layout').'</button>';
				}

				$output = '	<div class="float-right">
								'.$fork.'
							</div>
							<h3>'.$theme->zone_title($zone_id).' <div class="btn-group"><button type="button" class="btn btn-xs btn-success live-editor-add-row" data-toggle="tooltip" data-container="body" title="'.HB()->lang('New Row').'">'.icon('fas fa-plus').'</button></div></h3>'.
							$output;
			}

			$output = '<div'.($live_editor & \HB\HiddenCMS\Core\Output::ZONES ? ' class="live-editor-zone"' : '').' data-disposition-id="'.$disposition['disposition_id'].'">'.$output.'</div>';
		}

		return $output;
	}
}
