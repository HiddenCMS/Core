<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

function url($url = '')
{
	return HiddenCMS()->url($url);
}

function redirect($location = '')
{
	return HiddenCMS()->url->redirect(url($location));
}

function redirect_back($default = '')
{
	return redirect(HiddenCMS()->url->back() ?: $default);
}

function refresh()
{
	return HiddenCMS()->url->refresh();
}

function urltolink($url)
{
	return '<a href="'.$url.'">'.parse_url($url, PHP_URL_HOST).'</a>';
}
