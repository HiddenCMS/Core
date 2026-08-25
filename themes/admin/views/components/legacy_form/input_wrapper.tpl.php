<?php
$wrapper = ['ui', 'input'];

foreach (preg_split('/\s+/', trim((string)$classes)) as $token)
{
	if ($token !== '')
	{
		$wrapper[] = $token;
	}
}

if (!empty($icon) || $color)
{
	$wrapper[] = 'left icon';
}
?>
<div class="<?php echo implode(' ', array_values(array_unique(array_filter($wrapper)))) ?>">
	<?php echo $input; ?>
	<?php echo $color ? icon('fas fa-eye-dropper') : $icon; ?>
</div>
