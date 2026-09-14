<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

function url($url = '')
{
	if ($url === 'user/login')
	{
		$target = user_return_path($_SERVER['REQUEST_URI'] ?? '', HiddenCMS()->url->base);
		if ($target !== NULL) return HiddenCMS()->url($url).'?return='.rawurlencode($target);
	}
	return HiddenCMS()->url($url);
}

function user_return_path($value, $base = '/')
{
	if (!is_string($value) || strlen($value) > 2048 || substr($value, 0, 1) !== '/' || substr($value, 0, 2) === '//') return NULL;
	$decoded = rawurldecode($value);
	if (preg_match('/[\\x00-\\x20\\x7f\\\\\\\\]/', $decoded) || strpos($decoded, '//') === 0 || strpos($decoded, '%') !== FALSE) return NULL;
	$path = parse_url($decoded, PHP_URL_PATH);
	if (!is_string($path) || strpos($path, $base) !== 0 || preg_match('~(?:^|/)(?:\\.\\.?|ajax|install|vendor)(?:/|$)|(?:^|/)user/(?:login|register|lost-password|reset-password|auth|logout)(?:/|$)~i', $path)) return NULL;
	return $value;
}

function user_login_destination()
{
	$target = user_return_path(HiddenCMS()->session('auth_return'), HiddenCMS()->url->base);
	HiddenCMS()->session->set('auth_return', NULL);
	return $target !== NULL ? $target : HiddenCMS()->url();
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
