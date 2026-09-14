<?php
$services = privacy_services();
$consent_services = array_map(function($service){ return array_diff_key($service, ['title'=>TRUE, 'description'=>TRUE]); }, $services);
$config = ['services' => $services, 'prompt' => !HB()->url->admin, 'version' => hash('sha256', 'privacy-v1:'.json_encode($consent_services))];
?>
<script type="application/json" id="privacy-config"><?php echo json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<aside class="privacy-banner" id="privacy-banner" aria-labelledby="privacy-banner-title" hidden>
	<div><strong id="privacy-banner-title"><?php echo HB()->lang('Your privacy') ?></strong>
	<p><?php echo utf8_htmlentities((string)HB()->lang('With your consent, this site can enable these optional services: %s. You can decline and change your mind at any time through Manage cookies.', implode(', ', array_column($services, 'title')))) ?></p></div>
	<div class="privacy-actions">
		<button type="button" class="privacy-button" data-privacy-choice="reject"><?php echo HB()->lang('Reject all') ?></button>
		<button type="button" class="privacy-button" data-privacy-choice="accept"><?php echo HB()->lang('Accept all') ?></button>
		<button type="button" class="privacy-button" data-privacy-open><?php echo HB()->lang('Customize') ?></button>
	</div>
</aside>
<dialog class="privacy-dialog" id="privacy-dialog" aria-labelledby="privacy-title" aria-describedby="privacy-intro">
	<header><h2 id="privacy-title"><?php echo HB()->lang('Privacy and cookies') ?></h2><button type="button" class="privacy-close" data-privacy-close aria-label="<?php echo HB()->lang('Close') ?>">&times;</button></header>
	<div class="privacy-dialog-body">
		<p id="privacy-intro"><?php echo HB()->lang('Choose which services to allow. Declining does not block access to the site. Your choice is kept for six months in this browser and can be changed.') ?></p>
		<div class="privacy-necessary"><strong><?php echo HB()->lang('Site operation') ?></strong><span><?php echo HB()->lang('Always necessary') ?></span><p><?php echo HB()->lang('The sign-in session and saved preferences are not used for advertising.') ?></p></div>
		<?php foreach ($services as $id => $service): ?>
		<label class="privacy-service" for="privacy-service-<?php echo utf8_htmlentities($id) ?>">
			<span><strong><?php echo utf8_htmlentities($service['title']) ?></strong><span><?php echo utf8_htmlentities($service['description']) ?></span></span>
			<input type="checkbox" id="privacy-service-<?php echo utf8_htmlentities($id) ?>" data-privacy-toggle="<?php echo utf8_htmlentities($id) ?>">
		</label>
		<?php endforeach ?>
		<?php echo privacy_notice() ?>
		<p class="privacy-storage-warning" role="status" hidden><?php echo HB()->lang('Your browser cannot store this choice. You will be asked again on your next visit.') ?></p>
	</div>
	<footer class="privacy-actions">
		<button type="button" class="privacy-button" data-privacy-choice="reject"><?php echo HB()->lang('Reject all') ?></button>
		<button type="button" class="privacy-button" data-privacy-choice="accept"><?php echo HB()->lang('Accept all') ?></button>
		<button type="button" class="privacy-button" data-privacy-choice="save"><?php echo HB()->lang('Save my choices') ?></button>
	</footer>
</dialog>
<div class="privacy-fallback" id="privacy-fallback"><button type="button" class="privacy-preferences-link" data-privacy-open aria-label="<?php echo HB()->lang('Manage cookies') ?>" title="<?php echo HB()->lang('Manage cookies') ?>"><?php echo icon('fas fa-cookie-bite') ?></button></div>
<script src="<?php echo js('privacy.js').'?v='.filemtime(__DIR__.'/../../../dist/js/privacy.js') ?>" defer></script>
<?php if (isset($services['site_statistics'])): ?>
<script type="application/json" id="statistics-tracking-config"><?php echo json_encode(['url' => url('ajax/statistics/view.json'), 'token' => HB()->form()->token('statistics-view')], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
<script src="<?php echo js('site-statistics.js').'?v='.filemtime(__DIR__.'/../../../dist/js/site-statistics.js'); ?>" defer></script>
<?php endif; ?>
