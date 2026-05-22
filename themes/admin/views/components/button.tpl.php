<?php
$variant_map = [
	'primary'   => 'is-primary',
	'secondary' => 'is-light',
	'success'   => 'is-success',
	'danger'    => 'is-danger',
	'warning'   => 'is-warning',
	'info'      => 'is-info',
	'dark'      => 'is-dark',
	'light'     => 'is-white',
	'link'      => 'is-text'
];

$legacy_map = [
	'hb-btn-sm'      => 'is-small',
	'hb-btn-lg'      => 'is-medium',
	'hb-btn-block'   => 'is-fullwidth',
	'hb-btn-outline' => 'is-outlined',
	'hb-btn-icon'    => 'is-square',
	'hb-btn-link'    => 'is-text'
];

$tokens = array_values(array_filter(preg_split('/\s+/', trim((string)$class))));
$color_tokens = array_values(array_filter(preg_split('/\s+/', trim((string)$color))));
$classes = [];
$variant = '';

foreach (array_merge($tokens, $color_tokens) as $token)
{
	if (isset($variant_map[$token]))
	{
		$variant = $token;
		continue;
	}

	if (isset($legacy_map[$token]))
	{
		$classes[] = $legacy_map[$token];
		continue;
	}

	if (strpos($token, 'hb-btn-') === 0)
	{
		$suffix = substr($token, 7);

		if (isset($variant_map[$suffix]))
		{
			$variant = $suffix;
			continue;
		}
	}

	if ($token === 'hb-btn' || $token === 'btn')
	{
		continue;
	}

	$classes[] = $token;
}

$classes[] = 'button';
$classes[] = $variant_map[$variant ?: 'secondary'];

if ($disabled)
{
	$classes[] = 'is-static';
}

$final_class = implode(' ', array_values(array_unique(array_filter($classes))));
$attrs_output = $attrs_except_class.($final_class ? ' class="'.utf8_htmlentities($final_class).'"' : '');
echo '<'.$tag.$attrs_output.'>'.$content.'</'.$tag.'>';
?>
