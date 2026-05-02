<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace NF\NeoFrag\Actions;

class Popover extends \HB\HiddenCMS\Action
{
	protected function button($model)
	{
		return parent	::button()
						->title($model)
						->popover_ajax($this->url());
	}

	protected function action($model)
	{
		return $model->view('popovers/'.$model->__name);
	}
}


