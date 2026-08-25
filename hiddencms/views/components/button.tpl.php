<?php
$admin = $this->url->admin || (($theme = HB()->output->theme()) && $theme->info()->name == 'admin');

$legacy_size = '';
$legacy_fluid = FALSE;
$legacy_icon = FALSE;
$legacy_basic = FALSE;
$legacy_variant = '';
$extra_classes = [];

$tokens = array_merge(
	array_values(array_filter(preg_split('/\s+/', trim((string)$class)))),
	array_values(array_filter(preg_split('/\s+/', trim((string)$color))))
);

foreach ($tokens as $token)
{
	if ($token === 'ui' || $token === 'button' || $token === 'btn' || $token === 'badge')
	{
		continue;
	}

	if ($token === 'btn-sm' || $token === 'button-sm' || $token === 'btn-xs')
	{
		$legacy_size = 'mini';
		continue;
	}

	if ($token === 'btn-lg' || $token === 'button-lg')
	{
		$legacy_size = 'large';
		continue;
	}

	if ($token === 'btn-block' || $token === 'button-block')
	{
		$legacy_fluid = TRUE;
		continue;
	}

	if ($token === 'btn-outline' || $token === 'button-outline')
	{
		$legacy_basic = TRUE;
		continue;
	}

	if ($token === 'btn-icon' || $token === 'button-icon')
	{
		$legacy_icon = TRUE;
		continue;
	}

	if (preg_match('/^(?:btn|button|badge)-outline-(.+)$/', $token, $matches))
	{
		$legacy_basic = TRUE;
		$token = $matches[1];
	}
	else if (preg_match('/^text-(.+)$/', $token, $matches))
	{
		$legacy_basic = TRUE;
		$token = $matches[1];
	}
	else if (preg_match('/^(?:btn|button|badge)-(.+)$/', $token, $matches))
	{
		$token = $matches[1];
	}

	if ($legacy_variant === '' && in_array($token, ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark', 'light', 'link'], TRUE))
	{
		$legacy_variant = $token;
		continue;
	}

	$extra_classes[] = $token;
}

if ($admin)
{
	$color_map = [
		'primary'   => 'primary',
		'secondary' => 'secondary',
		'success'   => 'positive',
		'danger'    => 'negative',
		'warning'   => 'yellow',
		'info'      => 'teal',
		'dark'      => 'black',
		'light'     => 'basic',
		'link'      => 'basic'
	];

	$variant = $legacy_variant;
	$resolved_size = $size ?: $legacy_size;
	$basic = $outline || $legacy_basic;
	$resolved_fluid = $fluid || $legacy_fluid;
	$resolved_icon = $icon_only || $legacy_icon;

	if ($compact)
	{
		$resolved_size = $resolved_size ?: 'mini';
	}

	if (!$resolved_icon && preg_match('/^\s*<i\b[^>]*><\/i>\s*$/', trim((string)$content)))
	{
		$resolved_icon = TRUE;
	}

	$classes = ['ui'];

	if ($resolved_size)
	{
		$classes[] = $resolved_size;
	}

	if ($basic)
	{
		$classes[] = 'basic';
	}

	$classes[] = $variant ? $color_map[$variant] : 'secondary';

	if ($disabled)
	{
		$classes[] = 'disabled';
	}

	if ($resolved_fluid)
	{
		$classes[] = 'fluid';
	}

	if ($resolved_icon)
	{
		$classes[] = 'icon';
	}

	$classes = array_merge($classes, $extra_classes);
	$classes[] = 'button';
	$final_class = implode(' ', array_values(array_unique(array_filter($classes))));
}
else
{
	$classes = [];
	$visual_prefixes = ['btn-', 'button-', 'badge-', 'text-'];

	foreach ($tokens as $token)
	{
		if (in_array($token, ['btn', 'button', 'badge'], TRUE))
		{
			continue;
		}

		foreach ($visual_prefixes as $prefix)
		{
			if (strpos($token, $prefix) === 0)
			{
				continue 2;
			}
		}

		$classes[] = $token;
	}

	$final_class = trim(implode(' ', array_values(array_unique(array_filter($classes)))));
}

if ($admin && isset($attrs['data-toggle']) && $attrs['data-toggle'] === 'tooltip')
{
	if (isset($attrs['title']))
	{
		$attrs['data-tooltip'] = $attrs['title'];
	}

	unset($attrs['data-toggle'], $attrs['data-html'], $attrs['title']);
}

$attrs_output = '';

foreach ($attrs as $key => $value)
{
	$attrs_output .= ' '.$key.($value !== NULL ? '="'.utf8_htmlentities($value).'"' : '');
}

echo '<'.$tag.$attrs_output.($final_class ? ' class="'.utf8_htmlentities($final_class).'"' : '').'>'.$content.'</'.$tag.'>';
?>
