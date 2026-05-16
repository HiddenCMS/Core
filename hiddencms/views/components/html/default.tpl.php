<?php
$open_tag = '<'.$element_tag;

if ($element_attrs)
{
	$open_tag .= ' '.implode(' ', $element_attrs);
}

$open_tag .= '>';

echo $open_tag;

if ($element_content || $element_end_tag)
{
	echo $element_content.'</'.$element_tag.'>';
}

