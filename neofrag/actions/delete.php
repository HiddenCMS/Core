<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace NF\NeoFrag\Actions;

class Delete extends \HB\HiddenCMS\Action
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
		return $this->lang('ÃŠtes-vous sÃ»r.e de vouloir supprimer <b>%s</b> ?', $model);
	}
}


