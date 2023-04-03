<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Hidden\Actions;

use HD\Hidden\Action;

class Popover extends Action
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
