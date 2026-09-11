<section class="hc-media-text hc-media-text--image-<?php echo $settings['position'] ?> hc-media-text--align-<?php echo $settings['align'] ?>">
	<?php if ($image): ?>
	<div class="hc-media-text__media"><img src="<?php echo $image ?>" alt="<?php echo $settings['alt'] ?>" loading="lazy" /></div>
	<?php endif ?>
	<div class="hc-media-text__content">
		<?php if (!empty($settings['eyebrow'])): ?><div class="hc-media-text__eyebrow"><?php echo $settings['eyebrow'] ?></div><?php endif ?>
		<?php if (!empty($settings['heading'])): ?><h2><?php echo $settings['heading'] ?></h2><?php endif ?>
		<?php if (!empty($settings['content'])): ?><div class="hc-media-text__body"><?php echo $settings['content'] ?></div><?php endif ?>
		<?php if (!empty($settings['button']) && !empty($settings['link'])): ?><a class="hc-media-text__button" href="<?php echo $settings['link'] ?>"><?php echo $settings['button'] ?></a><?php endif ?>
	</div>
</section>
