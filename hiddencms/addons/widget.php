<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Addons;

use HB\HiddenCMS\Loadables\Addon;

abstract class Widget extends Addon
{
	const DISPLAY_TITLE_SETTING = '__display_title';

	static public $core = [
		'breadcrumb' => TRUE,
		'html'       => TRUE,
		'members'    => TRUE,
		'module'     => FALSE,
		'navigation' => TRUE,
		'user'       => TRUE
	];

	static public function __class($name)
	{
		return 'Widgets\\'.$name.'\\'.$name;
	}

	public function is_removable()
	{
		return !in_array($this->name, ['access', 'addons', 'admin', 'comments', 'live_editor', 'members', 'pages', 'search', 'settings', 'user']);
	}

	public function output($type = 'index', $settings = [])
	{
		if (is_array($type))
		{
			$settings = $type;
			$type     = 'index';
		}

		if (($controller = $this->controller('index')) && $controller->has_method($type))
		{
			return call_user_func_array([$controller, $type], [$settings]);
		}
	}

	public function get_admin($type, $settings = [])
	{
		$this->extract_display_title($settings);

		if (($controller = @$this->controller('admin')) && $controller->has_method($type))
		{
			if (!is_array($output = call_user_func_array([$controller, $type], [$settings])))
			{
				$output = [$output];
			}

			return $output;
		}

		return [];
	}

	public function get_settings($type, $settings = [])
	{
		if (($controller = @$this->controller('checker')) && $controller->has_method($type))
		{
			return $this->storage->encode(call_user_func_array([$controller, $type], [$settings]));
		}
	}

	public function extract_display_title(&$settings)
	{
		$display = !is_array($settings) || !array_key_exists(self::DISPLAY_TITLE_SETTING, $settings) || !empty($settings[self::DISPLAY_TITLE_SETTING]);

		if (is_array($settings))
		{
			unset($settings[self::DISPLAY_TITLE_SETTING]);
		}

		return $display;
	}

	public function title_is_displayed($settings)
	{
		while (is_string($settings) && $settings !== '')
		{
			$decoded = $this->storage->decode($settings, NULL);

			if ($decoded === NULL || $decoded === $settings)
			{
				break;
			}

			$settings = $decoded;
		}

		return !is_array($settings) || !array_key_exists(self::DISPLAY_TITLE_SETTING, $settings) || !empty($settings[self::DISPLAY_TITLE_SETTING]);
	}

	public function set_display_title($settings, $display)
	{
		$settings = $this->storage->decode($settings, []);

		if (!is_array($settings))
		{
			$settings = [];
		}

		$settings[self::DISPLAY_TITLE_SETTING] = $display ? 1 : 0;

		return $this->storage->encode($settings);
	}
}


