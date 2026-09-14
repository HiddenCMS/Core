<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

if (($date = $this->session('statistics', 'date')) && time() - $date > strtoseconds('1 day'))
{
	$this->session	->destroy('statistics', 'start')
					->destroy('statistics', 'end');
}

$rules = [
	[
		'type'  => 'legend',
		'label' => (string)$this->lang('Period')
	],
	'start' => [
		'type'  => 'date',
		'value' => $this->session('statistics', 'start') ?: strtotime('-1 year')
	],
	'end' => [
		'type'  => 'date',
		'value' => $this->session('statistics', 'end') ?: time()
	],
	'period' => [
		'type'   => 'select',
		'value'  => $this->session('statistics', 'period') ?: 'month',
		'values' => [
			'hour'  => (string)$this->lang('Hour'),
			'day'   => (string)$this->lang('Day'),
			'week'  => (string)$this->lang('Week'),
			'month' => (string)$this->lang('Month'),
			'year'  => (string)$this->lang('Year')
		]
	],
	[
		'type'  => 'legend',
		'label' => (string)$this->lang('Statistics')
	],
	'modules' => [
		'type'   => 'checkbox',
		'values' => []
	]
];

if ($modules = $this->session('statistics', 'modules'))
{
	$rules['modules']['checked'] = array_fill_keys($modules, TRUE);
}

foreach ($this->model()->get_statistics() as $name => $statistic)
{
	if ($modules === NULL)
	{
		$rules['modules']['checked'][$name] = TRUE;
	}

	$rules['modules']['values'][$name] = '<b style="color: '.$statistic['color'].'">'.$statistic['title'].'</b>';
}
