<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Hidden\Addons;

use HD\Hidden\Loadables\Addon;

abstract class Language extends Addon
{
	static public function __class($name)
	{
		return 'Addons\language_'.$name.'\language_'.$name;
	}

	abstract public function locale();
	abstract public function date2sql(&$date);
	abstract public function time2sql(&$time);
	abstract public function datetime2sql(&$datetime);

	public function __invoke($caller, $key, $source, $text)
	{
		$paths = [];

		$callback = function(&$path) use ($key){
			if (check_file($path))
			{
				$locales = include $path;

				if (array_key_exists($key, $locales))
				{
					$path = $locales[$key];
					return TRUE;
				}
			}
		};

		if ($locale = $caller->__path('langs', $this->info()->name.'.php', $paths, $callback))
		{
			return $locale;
		}
		else if (($result = Hidden()->collection('i18n')->where('lang_id', $this->__addon->id)->where('model', NULL)->where('name', $key)->row()) && $result())
		{
			return $result->value;
		}
		else if ($this->config->translate_api)
		{
			$source = Hidden()->model2('addon')->get('language', $source);

			$locale = $this	->network('https://i18n.neofr.ag')
							->auth($this->config->translate_api)
							->post([
								'source' => $source->info()->name,
								'text'   => $text,
								'target' => $this->info()->name
							]);

			if (!empty($locale->success))
			{
				Hidden()	->model2('i18n')
							->set('lang',  $this->__addon->id)
							->set('name',  $key)
							->set('value', $locale->success)
							->create();

				if (($result = Hidden()->collection('i18n')->where('lang_id', $lang = $source->__addon->id)->where('model', NULL)->where('name', $key)->row()) && !$result())
				{
					Hidden()	->model2('i18n')
								->set('lang',  $lang)
								->set('name',  $key)
								->set('value', $text)
								->create();
				}

				return $locale->success;
			}
		}

		trigger_error('Unfound lang: '.$key.' in paths ['.implode(';', $paths).']', E_USER_WARNING);
	}
}
