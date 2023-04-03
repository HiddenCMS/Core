<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Hidden\Actions;

use HD\Hidden\Action;

class Delete extends Action
{
	protected $_title = 'Supprimer';
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
		return $this->lang('Êtes-vous sûr.e de vouloir supprimer <b>%s</b> ?', $model);
	}
}
