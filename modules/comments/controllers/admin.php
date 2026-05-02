<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Comments\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Admin extends Controller_Module
{
	public function index($comments)
	{
		return $this->table2($comments, 'Aucun commentaire')
					->col('Module', function($comment){
						$info = $this->module(preg_replace('/_.*$/', '', $comment->module))->info();
						return HB()->label($info->title, $info->icon);
					})
					->col('Auteur', function($comment){
						return $comment->user->link();
					})
					->col('Date', 'date')
					->col('Message', 'content')
					->panel();
	}
}


