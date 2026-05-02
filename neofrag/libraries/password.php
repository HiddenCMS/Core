<?php
/**
 * https://neofr.ag
 * @author: MichaÃƒÂ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace NF\NeoFrag\Libraries;

use HB\HiddenCMS\Library;

class Password extends Library
{
	public function __construct($caller, $config = [])
	{
		parent::__construct($caller);
	}

	public function encrypt($password)
	{
		return password_hash($password, PASSWORD_DEFAULT);
	}

	public function is_valid($password, $stored_hash)
	{
		return password_verify($password, $stored_hash);
	}

	public function needs_rehash($stored_hash)
	{
		return password_needs_rehash($stored_hash, PASSWORD_DEFAULT);
	}
}


