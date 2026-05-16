<?php
$variants = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark', 'light', 'link'];
$tokens = array_values(array_filter(preg_split('/\s+/', trim((string)$class))));
$color_tokens = array_values(array_filter(preg_split('/\s+/', trim((string)$color))));
$classes = [];
$variant = '';

foreach (array_merge($tokens, $color_tokens) as $token)
{
	if (in_array($token, $variants, TRUE))
	{
		$variant = $token;
		continue;
	}

	$classes[] = $token;
}

$classes[] = 'hb-btn';
$classes[] = 'hb-btn-'.($variant ?: 'secondary');

if ($disabled)
{
	$classes[] = 'disabled';
}

$final_class = implode(' ', array_values(array_unique(array_filter($classes))));
$attrs_output = $attrs_except_class.($final_class ? ' class="'.utf8_htmlentities($final_class).'"' : '');
echo '<'.$tag.$attrs_output.'>'.$content.'</'.$tag.'>';
?>
