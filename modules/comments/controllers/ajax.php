<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Comments\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Ajax extends Controller_Module
{
	public function delete($comment_id, $module_id, $module)
	{
		$this	->title($this->lang('Confirmation de suppression'))
				->form()
				->confirm_deletion($this->lang('Confirmation de suppression'), $this->lang('ÃŠtes-vous sÃ»r(e) de vouloir supprimer ce commentaire ?'));

		if ($this->form()->is_valid())
		{
			if ($this->db->select('id')->from('comment')->where('module', $module)->where('module_id', $module_id)->order_by('id DESC')->row() == $comment_id)
			{
				$this->db	->where('id', $comment_id)
							->delete('comment');
			}
			else
			{
				$this->db	->where('id', $comment_id)
							->update('comment', [
								'content' => NULL
							]);
			}

			return 'OK';
		}

		return $this->form()->display();
	}
}


