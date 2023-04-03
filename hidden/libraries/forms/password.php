<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Hidden\Libraries\Forms;

class Password extends Text
{
	protected $_type = 'password';

	public function __invoke($name)
	{
		return parent	::__invoke($name)
						->addon('fas fa-lock');
	}
}
