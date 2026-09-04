<?php
$services = privacy_services();
$config = ['services' => $services, 'version' => hash('sha256', 'privacy-v1:'.json_encode($services))];
?>
<script type="application/json" id="privacy-config"><?php echo json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<aside class="privacy-banner" id="privacy-banner" aria-labelledby="privacy-banner-title" hidden>
	<div><strong id="privacy-banner-title">Votre confidentialit&eacute;</strong>
	<p>Avec votre accord, ce site peut charger <?php echo isset($services['analytics']) ? 'Google Analytics pour mesurer la fr&eacute;quentation et ' : '' ?>des contenus externes (<?php echo utf8_htmlentities(implode(', ', array_column(array_filter($services, function($key){ return $key !== 'analytics'; }, ARRAY_FILTER_USE_KEY), 'title'))) ?>). Vous pouvez refuser et changer d&rsquo;avis &agrave; tout moment dans &laquo; G&eacute;rer mes cookies &raquo;.</p></div>
	<div class="privacy-actions">
		<button type="button" class="privacy-button" data-privacy-choice="reject">Tout refuser</button>
		<button type="button" class="privacy-button" data-privacy-choice="accept">Tout accepter</button>
		<button type="button" class="privacy-button" data-privacy-open>Personnaliser</button>
	</div>
</aside>
<dialog class="privacy-dialog" id="privacy-dialog" aria-labelledby="privacy-title" aria-describedby="privacy-intro">
	<header><h2 id="privacy-title">Confidentialit&eacute; et cookies</h2><button type="button" class="privacy-close" data-privacy-close aria-label="Fermer">&times;</button></header>
	<div class="privacy-dialog-body">
		<p id="privacy-intro">Choisissez les services autoris&eacute;s. Refuser ne bloque pas l&rsquo;acc&egrave;s au site. Votre choix est conserv&eacute; six mois dans ce navigateur et reste modifiable.</p>
		<div class="privacy-necessary"><strong>Fonctionnement du site</strong><span>Toujours n&eacute;cessaire</span><p>La session de connexion et la m&eacute;morisation de vos pr&eacute;f&eacute;rences ne servent pas &agrave; la publicit&eacute;.</p></div>
		<?php foreach ($services as $id => $service): ?>
		<label class="privacy-service" for="privacy-service-<?php echo utf8_htmlentities($id) ?>">
			<span><strong><?php echo utf8_htmlentities($service['title']) ?></strong><span><?php echo utf8_htmlentities($service['description']) ?></span></span>
			<input type="checkbox" id="privacy-service-<?php echo utf8_htmlentities($id) ?>" data-privacy-toggle="<?php echo utf8_htmlentities($id) ?>">
		</label>
		<?php endforeach ?>
		<?php echo privacy_notice() ?>
		<p class="privacy-storage-warning" role="status" hidden>Votre navigateur ne permet pas de conserver ce choix. Il sera redemand&eacute; &agrave; la prochaine visite.</p>
	</div>
	<footer class="privacy-actions">
		<button type="button" class="privacy-button" data-privacy-choice="reject">Tout refuser</button>
		<button type="button" class="privacy-button" data-privacy-choice="accept">Tout accepter</button>
		<button type="button" class="privacy-button" data-privacy-choice="save">Enregistrer mes choix</button>
	</footer>
</dialog>
<div class="privacy-fallback" id="privacy-fallback"><?php echo privacy_preferences_link() ?></div>
<script src="<?php echo js('privacy.js') ?>" defer></script>
