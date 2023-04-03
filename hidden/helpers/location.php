<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

function url($url = '')
{
	return Hidden()->url($url);
}

function redirect($location = '')
{
	return Hidden()->url->redirect(url($location));
}

function redirect_back($default = '')
{
	return redirect(Hidden()->url->back() ?: $default);
}

function refresh()
{
	return Hidden()->url->refresh();
}

function urltolink($url)
{
	return '<a href="'.$url.'">'.parse_url($url, PHP_URL_HOST).'</a>';
}
