<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Hidden\Libraries\Forms;

use HD\Hidden\Library;

class Legend extends Library
{
	protected $_label;

	public function __invoke($label, $icon = '')
	{
		$this->_label = is_a($label, 'HD\Hidden\Libraries\Label') ? $label : $this->label($label, $icon);
		return $this;
	}

	public function __toString()
	{
		return '<legend>
					<div class="form-legend">
						'.$this->_label.'
					</div>
				</legend>';
	}
}
