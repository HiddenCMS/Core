<?php
$fallback_attrs = $element_attrs ? ' '.implode(' ', $element_attrs) : '';
$fallback = '<'.$element_tag.$fallback_attrs.'>'.$element_content.'</'.$element_tag.'>';

echo HB()->template->render('forms/select', [
	'element_tag'     => $element_tag,
	'element_attrs'   => $element_attrs,
	'element_content' => $element_content
], $fallback);
