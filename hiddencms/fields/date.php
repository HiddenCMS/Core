<?php
/**
 * https://neofr.ag
 * @author: MichaÃƒÂ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Fields;

#[\AllowDynamicProperties]
class Date extends DateTime
{
	public function raw($value)
	{
		return substr(parent::raw($value), 0, 10);
	}
}
