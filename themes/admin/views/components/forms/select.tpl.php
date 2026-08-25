<?php
if (isset($element_attrs))
{
	$class_index = NULL;
	$classes = [];
	$search = FALSE;

	foreach ($element_attrs as $index => $attribute)
	{
		if ($attribute === 'data-search')
		{
			$search = TRUE;
		}

		if (preg_match('/^class="([^"]*)"$/', $attribute, $match))
		{
			$class_index = $index;
			$classes = preg_split('/\s+/', trim($match[1]));
			break;
		}
	}

	$classes = array_values(array_unique(array_filter(array_merge($classes, ['ui', 'fluid', $search ? 'search' : '', 'dropdown']))));
	$class_attribute = 'class="'.utf8_htmlentities(implode(' ', $classes)).'"';

	if ($class_index === NULL)
	{
		$element_attrs[] = $class_attribute;
	}
	else
	{
		$element_attrs[$class_index] = $class_attribute;
	}
?>
	<<?php echo $element_tag; ?><?php echo $element_attrs ? ' '.implode(' ', $element_attrs) : ''; ?>><?php echo $element_content; ?></<?php echo $element_tag; ?>>
<?php
}
else
{
?>
	<select<?php echo $attrs_output ?>></select>
<?php
}
