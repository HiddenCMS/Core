<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Hidden\Fields;

class File
{
	public function init($field)
	{
		$field->depends('file', '');
	}
}
