<?php
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

$tokens = array_merge(
	array_values(array_filter(preg_split('/\s+/', trim((string)$class)))),
	array_values(array_filter(preg_split('/\s+/', trim((string)$color))))
);

$variant = '';
$size = '';
$basic = FALSE;
$fluid = FALSE;
$icon = FALSE;
$extra_classes = [];

foreach ($tokens as $token)
{
	if ($token === 'ui' || $token === 'button' || $token === 'btn' || $token === 'badge')
	{
		continue;
	}

	if ($token === 'btn-sm' || $token === 'button-sm' || $token === 'btn-xs')
	{
		$size = 'mini';
		continue;
	}

	if ($token === 'btn-lg' || $token === 'button-lg')
	{
		$size = 'large';
		continue;
	}

	if ($token === 'btn-block' || $token === 'button-block')
	{
		$fluid = TRUE;
		continue;
	}

	if ($token === 'btn-outline' || $token === 'button-outline')
	{
		$basic = TRUE;
		continue;
	}

	if ($token === 'btn-icon' || $token === 'button-icon')
	{
		$icon = TRUE;
		continue;
	}

	if (preg_match('/^(?:btn|button|badge)-outline-(.+)$/', $token, $matches))
	{
		$basic = TRUE;
		$token = $matches[1];
	}
	else if (preg_match('/^text-(.+)$/', $token, $matches))
	{
		$basic = TRUE;
		$token = $matches[1];
	}
	else if (preg_match('/^(?:btn|button|badge)-(.+)$/', $token, $matches))
	{
		$token = $matches[1];
	}

	if (isset($color_map[$token]))
	{
		$variant = $token;
		continue;
	}

	$extra_classes[] = $token;
}

if ($compact)
{
	$size = $size ?: 'mini';
}

if ($outline)
{
	$basic = TRUE;
}

$classes = ['ui'];

if ($size)
{
	$classes[] = $size;
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

if ($fluid)
{
	$classes[] = 'fluid';
}

if ($icon)
{
	$classes[] = 'icon';
}

$classes = array_merge($classes, $extra_classes);
$classes[] = 'button';
$final_class = implode(' ', array_values(array_unique(array_filter($classes))));

echo '<'.$tag.$attrs_except_class.' class="'.utf8_htmlentities($final_class).'">'.$content.'</'.$tag.'>';
?>
