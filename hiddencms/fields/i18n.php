<?php
/**
 * https://neofr.ag
 * @author: MichaÃƒÆ’Ã‚Â«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Fields;

#[\AllowDynamicProperties]
class I18n
{
	protected $_multiline = FALSE;

	public function __construct($type = '')
	{
		if ($type == 'multiline')
		{
			$this->_multiline = TRUE;
		}
	}

	public function value($value, $model, $field)
	{
		foreach (HB()->config->langs as $lang)
		{
			$value = HB()	->collection('i18n')
								->where('lang_id',  $lang->__addon->id)
								->where('model',    $model->__table)
								->where('model_id', $model->id)
								->where('name',     $field->name)
								->row();

			if ($value())
			{
				break;
			}
		}

		return $value;
	}
}

