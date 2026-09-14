<details class="site-language-selector">
<summary class="site-language-toggle" aria-label="<?php echo $this->lang('Choose language') ?>" title="<?php echo $this->lang('Choose language') ?>"><?php echo icon('fas fa-globe') ?></summary>
<nav class="site-language-menu" aria-label="<?php echo $this->lang('Languages') ?>">
<?php foreach ($languages as $language): ?>
<a href="<?php echo utf8_htmlentities($language['url'], ENT_QUOTES) ?>" hreflang="<?php echo utf8_htmlentities($language['code'], ENT_QUOTES) ?>" lang="<?php echo utf8_htmlentities($language['code'], ENT_QUOTES) ?>"<?php echo $language['active'] ? ' aria-current="true"' : '' ?>>
<?php if ($language['flag']): ?><img src="<?php echo utf8_htmlentities($language['flag'], ENT_QUOTES) ?>" width="24" height="16" alt=""><?php else: ?><?php echo icon('fas fa-globe') ?><?php endif ?>
<span><?php echo utf8_htmlentities($language['title']) ?></span>
<?php if ($language['active']): ?><?php echo icon('fas fa-check') ?><?php endif ?>
</a>
<?php endforeach ?>
</nav>
</details>
