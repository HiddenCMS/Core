<?php if ($tag === 'button'): ?>
<button class="btn btn-<?php echo $variant === 'primary' ? 'primary' : 'secondary'; ?>" type="<?php echo $type; ?>"><?php echo $label; ?></button>
<?php else: ?>
<a href="<?php echo $url; ?>" class="btn btn-<?php echo $variant === 'primary' ? 'primary' : 'secondary'; ?>"><?php echo $label; ?></a>
<?php endif; ?>
