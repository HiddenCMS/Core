<?php if ($tag === 'button'): ?>
<button class="ui <?php echo $variant; ?> button" type="<?php echo $type; ?>"><?php echo $label; ?></button>
<?php else: ?>
<a href="<?php echo $url; ?>" class="ui <?php echo $variant; ?> button"><?php echo $label; ?></a>
<?php endif; ?>
