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
		$classes = HB()->disposition->normalize_classes($output->zone_classes ?? '');

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
						? '<button type="button" class="btn btn-link live-editor-fork" data-enabled="0">'.icon('fas fa-toggle-off').' '.HB()->lang('Disposition commune').'</button>'
						: '<button type="button" class="btn btn-link live-editor-fork" data-enabled="1">'.icon('fas fa-toggle-on').' '.HB()->lang('Disposition specifique a la page').'</button>';
				}

				$output = '	<div class="float-right">
								<button type="button" class="btn btn-link live-editor-zone-classes" title="Classes CSS">'.icon('fas fa-paint-brush').'</button>
								'.$fork.'
							</div>
							<h3>'.(!empty($theme->info()->zones[$zone_id]) ? $theme->info()->zones[$zone_id] : HB()->lang('Zone #%d', $zone_id)).' <div class="btn-group"><button type="button" class="btn btn-xs btn-success live-editor-add-row" data-toggle="tooltip" data-container="body" title="'.HB()->lang('Nouveau Row').'">'.icon('fas fa-plus').'</button></div></h3>'.
							$output;
			}

			$output = '<div class="'.utf8_htmlentities(trim('hc-zone '.$classes.($live_editor & \HB\HiddenCMS\Core\Output::ZONES ? ' live-editor-zone' : ''))).'" data-zone-classes="'.utf8_htmlentities($classes).'" data-disposition-id="'.$disposition['disposition_id'].'">'.$output.'</div>';
		}
		else if ($classes)
		{
			$output = '<div class="hc-zone '.utf8_htmlentities($classes).'">'.$output.'</div>';
		}
		if ($classes) { HB()->css('zone_helpers'); }

		return $output;
	}
}
