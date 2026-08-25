<?php
$classes = ['ui', 'input'];

if ($prepend && !$append)
{
	$classes[] = 'left labeled';
}
else if ($append && !$prepend)
{
	$classes[] = 'right labeled';
}
else if ($prepend || $append)
{
	$classes[] = 'labeled';
}
?>
<div class="<?php echo implode(' ', $classes) ?>">
	<?php foreach ($prepend as $addon): ?>
		<div class="ui label"><?php echo $addon ?></div>
	<?php endforeach ?>
	<?php echo $input ?>
	<?php foreach ($append as $addon): ?>
		<div class="ui label"><?php echo $addon ?></div>
	<?php endforeach ?>
</div>
