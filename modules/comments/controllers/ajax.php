<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Comments\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Ajax extends Controller_Module
{
	public function delete($comment_id, $module_id, $module)
	{
		$this	->title($this->lang('Confirm deletion'))
				->form()
				->confirm_deletion($this->lang('Confirm deletion'), $this->lang('Are you sure you want to delete this comment?'));

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


