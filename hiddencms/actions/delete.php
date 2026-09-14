<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Actions;

class Delete extends \HB\HiddenCMS\Action
{
	protected $_title = 'Delete';
	protected $_icon  = 'far fa-trash-alt';
	protected $_color = 'danger';

	protected function action($model)
	{
		return $this->modal_delete('Suppression', $this->_icon ?: $model::$icon)
					->body($this->message($model))
					->callback(function() use ($model){
						$model->delete();
						refresh();
					});
	}

	protected function message($model)
	{
		return $this->lang('Are you sure you want to delete <b>%s</b>?', $model);
	}
}


