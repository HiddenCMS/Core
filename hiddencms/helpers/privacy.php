<?php

function privacy_profile_fields()
{
	return [
		'first_name' => 'Prénom', 'last_name' => 'Nom',
		'date_of_birth' => 'Naissance (âge public)', 'sex' => 'Sexe',
		'country' => 'Pays', 'location' => 'Localisation'
	];
}

function privacy_profile_mode($field)
{
	if (!array_key_exists($field, privacy_profile_fields())) return 'disabled';
	$key = 'privacy_profile_'.$field;
	// Existing installations keep their behavior until the administrator chooses.
	$mode = HB()->config->$key ?? 'public';
	return in_array($mode, ['disabled', 'private', 'public'], TRUE) ? $mode : 'disabled';
}

function privacy_profile_collects($field)
{
	return privacy_profile_mode($field) !== 'disabled';
}

function privacy_profile_value($profile, $field)
{
	return privacy_profile_mode($field) === 'public' ? $profile->$field : NULL;
}

function privacy_pages()
{
	$pages = [];
	$module = @HB()->module('pages');
	if (!$module || !$module->is_enabled()) return $pages;
	foreach ($module->model()->get_pages() as $page)
	{
		if ($page['published'] && HB()->access('pages', 'access_page', $page['page_id'], 'visitors'))
		{
			$pages[(string)$page['page_id']] = $page;
		}
	}
	return $pages;
}

function privacy_policy_url()
{
	$id = (string)(HB()->config->privacy_page ?? '');
	if (!$id || !ctype_digit($id)) return '';
	$pages = privacy_pages();
	if (!isset($pages[$id]) || !preg_match('~^[a-z0-9]+(?:[-/][a-z0-9]+)*$~i', $pages[$id]['name'])) return '';
	return url($pages[$id]['name']);
}

function privacy_notice()
{
	$links = [];
	if ($url = privacy_policy_url())
	{
		$links[] = '<a href="'.utf8_htmlentities($url).'" target="_blank" rel="noopener">'.HB()->lang('Politique de confidentialité').'</a>';
	}
	$email = html_entity_decode((string)(HB()->config->privacy_contact ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
	if (filter_var($email, FILTER_VALIDATE_EMAIL))
	{
		$label = HB()->lang('Contact confidentialité');
		$owner = html_entity_decode((string)(HB()->config->privacy_controller ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
		if ($owner !== '') $label .= ' : '.utf8_htmlentities($owner);
		$links[] = '<a href="mailto:'.utf8_htmlentities($email).'">'.$label.'</a>';
	}
	return $links ? '<p class="privacy-notice">'.implode(' &middot; ', $links).'</p>' : '';
}

function privacy_analytics_id()
{
	$id = trim((string)(HB()->config->analytics ?? ''));
	return !HB()->url->admin && preg_match('/^G-[A-Z0-9]+$/D', $id) ? $id : '';
}

// Addons register their services before the main template renders its preferences.
function privacy_services($id = NULL, $definition = NULL)
{
	static $addons = [];
	if ($id !== NULL)
	{
		if (!preg_match('/^[a-z][a-z0-9_-]+$/D', $id) || in_array($id, ['analytics', 'youtube'], TRUE)
			|| !is_array($definition) || empty($definition['title']) || empty($definition['description']) || empty($definition['hosts']))
		{
			throw new InvalidArgumentException('Invalid privacy service');
		}
		$addons[$id] = $definition;
	}
	$services = ['youtube' => [
		'title' => 'Vidéos YouTube',
		'description' => 'Google / YouTube lit les vidéos intégrées et peut utiliser des traceurs. Sans accord, les vidéos restent bloquées.',
		'hosts' => ['www.youtube.com', 'youtube.com', 'www.youtube-nocookie.com', 'youtube-nocookie.com']
	]];
	if ($analytics = privacy_analytics_id()) $services['analytics'] = [
		'title' => 'Mesure d’audience',
		'description' => 'Google Analytics mesure la fréquentation et les interactions sur ce site. Sans accord, aucune mesure Google Analytics n’est chargée.',
		'hosts' => ['www.googletagmanager.com'], 'measurementId' => $analytics
	];
	return array_merge($services, $addons);
}

function privacy_preferences_link()
{
	return '<button type="button" class="privacy-preferences-link" data-privacy-open>'.HB()->lang('Gérer mes cookies').'</button>';
}

function privacy_embed($service, $src, $title = '')
{
	$definition = privacy_services()[$service] ?? NULL;
	$parts = parse_url($src);
	if (!$definition || !$parts || ($parts['scheme'] ?? '') !== 'https' || isset($parts['user']) || isset($parts['pass'])
		|| isset($parts['port']) || !in_array(strtolower($parts['host'] ?? ''), $definition['hosts'], TRUE)) return '';
	$title = $title ?: $definition['title'];
	return '<div class="privacy-embed" data-privacy-service="'.utf8_htmlentities($service, ENT_QUOTES).'" data-privacy-src="'.utf8_htmlentities($src, ENT_QUOTES).'" data-privacy-title="'.utf8_htmlentities($title, ENT_QUOTES).'">'
		.'<div class="privacy-embed-placeholder"><strong>'.utf8_htmlentities($title).'</strong><p>'.HB()->lang('Ce contenu externe est bloqué selon vos préférences de confidentialité.').'</p>'
		.'<button type="button" class="privacy-button" data-privacy-open>'.HB()->lang('Choisir mes préférences').'</button>'
		.'<noscript><p>'.HB()->lang('JavaScript est nécessaire pour autoriser ce contenu.').'</p></noscript></div></div>';
}

// Filter rich-text embeds on the server: a client-side observer would be too late.
function privacy_media_html($html)
{
	if (stripos($html, '<iframe') === FALSE) return $html;
	$document = new DOMDocument('1.0', 'UTF-8');
	$previous = libxml_use_internal_errors(TRUE);
	try
	{
		$document->loadHTML('<?xml encoding="UTF-8"><html><body>'.$html.'</body></html>', LIBXML_NONET);
		$changed = FALSE;
		foreach (iterator_to_array($document->getElementsByTagName('iframe')) as $frame)
		{
			$src = $frame->getAttribute('src');
			if (strpos($src, '//') === 0) $src = 'https:'.$src;
			$host = strtolower((string)parse_url($src, PHP_URL_HOST));
			foreach (privacy_services() as $service => $definition)
			{
				if ($service === 'analytics' || !in_array($host, $definition['hosts'], TRUE)) continue;
				if (strpos($src, 'http:') === 0) $src = 'https:'.substr($src, 5);
				$placeholder = privacy_embed($service, $src, $frame->getAttribute('title'));
				$fragment = new DOMDocument('1.0', 'UTF-8');
				$fragment->loadHTML('<?xml encoding="UTF-8"><html><body>'.$placeholder.'</body></html>', LIBXML_NONET);
				$replacement = $fragment->getElementsByTagName('body')->item(0)->firstChild;
				if ($replacement) $frame->parentNode->replaceChild($document->importNode($replacement, TRUE), $frame);
				else $frame->parentNode->removeChild($frame);
				$changed = TRUE;
				break;
			}
		}
		if (!$changed) return $html;
		$result = '';
		foreach ($document->getElementsByTagName('body')->item(0)->childNodes as $child) $result .= $document->saveHTML($child);
		return $result;
	}
	finally
	{
		libxml_clear_errors();
		libxml_use_internal_errors($previous);
	}
}
