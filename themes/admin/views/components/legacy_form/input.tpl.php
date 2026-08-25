<input<?php foreach ($attrs as $key => $value): ?> <?php echo $key; ?><?php if ($value !== NULL): ?>="<?php echo utf8_htmlentities($value); ?>"<?php endif ?><?php endforeach; ?> />
