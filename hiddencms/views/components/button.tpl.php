<?php
$tokens = array_merge(
	array_values(array_filter(preg_split('/\s+/', trim((string)$class)))),
	array_values(array_filter(preg_split('/\s+/', trim((string)$color))))
);

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

echo '<'.$tag.$attrs_except_class.($final_class ? ' class="'.utf8_htmlentities($final_class).'"' : '').'>'.$content.'</'.$tag.'>';
?>
