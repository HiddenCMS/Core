<?php
$label_tag = !empty($label_tag) ? $label_tag : 'label';

$classes = ['field'];

if ($required)
{
	$classes[] = 'required';
}

if ($has_errors)
{
	$classes[] = 'error';
}

foreach (preg_split('/\s+/', trim((string)$size)) as $token)
{
	if ($token !== '')
	{
		$classes[] = $token;
	}
}
?>
<div class="<?php echo implode(' ', array_values(array_unique(array_filter($classes)))) ?>">
	<?php if ($label_html && !$compact): ?>
		<<?php echo $label_tag ?><?php if ($label_for): ?> for="<?php echo $label_for ?>"<?php endif ?>><?php echo $label_html ?></<?php echo $label_tag ?>>
	<?php endif ?>
	<?php echo $input ?>
	<?php if ($compact_errors_html): ?>
		<?php echo $compact_errors_html ?>
	<?php endif ?>
</div>
