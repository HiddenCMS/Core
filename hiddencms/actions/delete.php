<?php
/**
 * https://neofr.ag
 * @author: MichaÃƒÆ’Ã‚Â«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Actions;

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
		return $this->lang('ÃƒÆ’Ã…Â tes-vous sÃƒÆ’Ã‚Â»r.e de vouloir supprimer <b>%s</b> ?', $model);
	}
}


