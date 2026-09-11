<figure class="hc-image hc-image--<?php echo $settings['ratio'] ?>">
	<?php if (!empty($settings['link'])): ?><a href="<?php echo $settings['link'] ?>"><?php endif ?>
	<img src="<?php echo $image ?>" alt="<?php echo $settings['alt'] ?>" loading="lazy" />
	<?php if (!empty($settings['link'])): ?></a><?php endif ?>
	<?php if (!empty($settings['caption'])): ?><figcaption><?php echo $settings['caption'] ?></figcaption><?php endif ?>
</figure>
