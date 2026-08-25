<?php
$label_tag = !empty($label_tag) ? $label_tag : 'label';

$semantic_width = function($size){
	$size = max(1, min(12, (int)$size));
	$width = max(1, min(16, (int)round($size * 16 / 12)));
	$words = [
		1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four',
		5 => 'five', 6 => 'six', 7 => 'seven', 8 => 'eight',
		9 => 'nine', 10 => 'ten', 11 => 'eleven', 12 => 'twelve',
		13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen', 16 => 'sixteen'
	];

	return $words[$width];
};

$classes = ['field'];

if ($display_required && $is_required)
{
	$classes[] = 'required';
}

if ($has_error)
{
	$classes[] = 'error';
}

foreach (preg_split('/\s+/', trim((string)$size)) as $token)
{
	if (preg_match('/^col-(\d+)$/', $token, $match))
	{
		$classes[] = $semantic_width($match[1]).' wide';
	}
	else if ($token !== '')
	{
		$classes[] = $token;
	}
}
?>
<div class="<?php echo implode(' ', array_values(array_unique(array_filter($classes)))) ?>">
	<?php if (!$fast_mode && $label): ?>
		<<?php echo $label_tag ?><?php if ($label_for): ?> for="<?php echo utf8_htmlentities($label_for) ?>"<?php endif ?><?php if ($description || $error): ?> data-tooltip="<?php echo utf8_htmlentities(trim(strip_tags(implode(' ', array_filter([$description, $error]))))); ?>" data-position="top left"<?php endif ?>>
			<?php if ($error): ?><?php echo icon('fas fa-exclamation-triangle'); ?><?php elseif ($description): ?><?php echo icon('fas fa-info-circle'); ?><?php endif ?>
			<?php echo $label; ?>
		</<?php echo $label_tag ?>>
	<?php endif; ?>
	<?php echo $content; ?>
</div>
