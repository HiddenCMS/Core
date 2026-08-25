<?php
$label_tag = !empty($label_tag) ? $label_tag : 'label';

$semantic_width = function($size){
	$size = max(1, min(12, (int)$size));
	$width = max(1, min(16, (int)round($size * 16 / 12)));
	$words = [
		1 => 'one',
		2 => 'two',
		3 => 'three',
		4 => 'four',
		5 => 'five',
		6 => 'six',
		7 => 'seven',
		8 => 'eight',
		9 => 'nine',
		10 => 'ten',
		11 => 'eleven',
		12 => 'twelve',
		13 => 'thirteen',
		14 => 'fourteen',
		15 => 'fifteen',
		16 => 'sixteen'
	];

	return $words[$width];
};

$classes = ['field'];

if ($required)
{
	$classes[] = 'required';
}

foreach (preg_split('/\s+/', trim((string)$size)) as $token)
{
	if (preg_match('/^col-(\d+)$/', $token, $match))
	{
		$classes[] = $semantic_width($match[1]).' wide';
		continue;
	}

	if ($token !== '')
	{
		$classes[] = $token;
	}
}

if ($has_errors)
{
	$classes[] = 'error';
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
