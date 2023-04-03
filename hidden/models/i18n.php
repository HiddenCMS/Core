<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Hidden\Models;

use HD\Hidden\Loadables\Model2;

class I18n extends Model2
{
	static public function __schema()
	{
		return [
			'id'       => self::field()->primary(),
			'lang'     => self::field()->depends('addon'),
			'model'    => self::field()->text(100)->null(),
			'model_id' => self::field()->int()->null(),
			'name'     => self::field()->text(100),
			'value'    => self::field()->text()
		];
	}

	public function __invoke()
	{
		return parent::__invoke() && $this->value;
	}

	public function __toString()
	{
		$prefix = '';

		if (HIDDEN_LOGS_I18N && $this->lang())
		{
			$prefix = $this->lang->addon()->info()->icon;
		}

		return $prefix.$this->value;
	}
}
