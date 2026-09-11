<section class="hc-hero hc-hero--<?php echo $settings['height'] ?> hc-hero--<?php echo $settings['align'] ?> hc-hero--overlay-<?php echo $settings['overlay'] ?>"<?php if ($image): ?> style="background-image:url('<?php echo utf8_htmlentities($image) ?>')"<?php endif ?>>
	<div class="hc-hero__content">
		<?php if (!empty($settings['eyebrow'])): ?><div class="hc-hero__eyebrow"><?php echo $settings['eyebrow'] ?></div><?php endif ?>
		<?php if (!empty($settings['heading'])): ?><h2><?php echo $settings['heading'] ?></h2><?php endif ?>
		<?php if (!empty($settings['content'])): ?><p><?php echo nl2br($settings['content']) ?></p><?php endif ?>
		<?php if (!empty($settings['button']) && !empty($settings['link'])): ?><a class="hc-hero__button" href="<?php echo $settings['link'] ?>"><?php echo $settings['button'] ?></a><?php endif ?>
	</div>
</section>
