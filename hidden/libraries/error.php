<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Hidden\Libraries;

use HD\Hidden\Library;

class Error extends Library
{
	public function __invoke()
	{
		throw Hidden()->___load('', 'exception', [function(){
			header('HTTP/1.0 404 Not Found');
			return $this->view('errors/unfound');
		}]);
	}

	public function __call($name, $args)
	{
		if ($name == 'throw')
		{
			throw Hidden()->___load('', 'exception', $args);
		}

		return parent::__call($name, $args);
	}

	public function unauthorized()
	{
		throw Hidden()->___load('', 'exception', [function(){
			header('HTTP/1.0 403 Forbidden');
			return $this->view('errors/unauthorized');
		}]);
	}

	public function unconnected()
	{
		if (!$this->user())
		{
			throw Hidden()->___load('', 'exception', [function(){
				header('HTTP/1.0 401 Unauthorized');
				$this->session->append('modals', 'ajax/user/login');
				redirect();
			}]);
		}
	}
}
