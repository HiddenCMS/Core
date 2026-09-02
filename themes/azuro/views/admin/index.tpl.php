<div class="theme-customizer">
	<nav class="theme-customizer-nav" aria-label="<?php echo $this->lang('Sections de personnalisation') ?>">
		<div class="theme-customizer-nav-title">
			<?php echo icon('fas fa-sliders-h').' '.$this->lang('Sections') ?>
		</div>
		<a class="theme-customizer-tab active" href="#dashboard" data-tab-target="dashboard" aria-selected="true">
			<?php echo icon('fas fa-info-circle') ?>
			<span><?php echo $this->lang('Vue d’ensemble') ?></span>
		</a>
		<a class="theme-customizer-tab" href="#background" data-tab-target="background" aria-selected="false">
			<?php echo icon('far fa-image') ?>
			<span><?php echo $this->lang('En-tête du site') ?></span>
		</a>
		<a class="theme-customizer-tab" href="#colors" data-tab-target="colors" aria-selected="false">
			<?php echo icon('fas fa-palette') ?>
			<span><?php echo $this->lang('Couleurs') ?></span>
		</a>
	</nav>

	<div class="theme-customizer-content">
		<section class="theme-customizer-pane active" data-tab="dashboard">
			<header class="theme-customizer-heading">
				<?php echo icon('fas fa-info-circle') ?>
				<h2><?php echo $this->lang('Vue d’ensemble') ?></h2>
			</header>

			<div class="theme-overview">
				<figure class="theme-preview">
					<img src="<?php echo url($this->__caller->__path('', 'thumbnail.png')) ?>" alt="<?php echo utf8_htmlentities($theme->title) ?>" />
				</figure>
				<dl class="theme-metadata">
					<div><dt><?php echo $this->lang('Nom du thème') ?></dt><dd><?php echo utf8_htmlentities($theme->title) ?></dd></div>
					<div><dt><?php echo $this->lang('Description') ?></dt><dd><?php echo utf8_htmlentities($theme->description) ?></dd></div>
					<div><dt><?php echo $this->lang('Version') ?></dt><dd><code><?php echo utf8_htmlentities($theme->version) ?></code></dd></div>
					<div><dt><?php echo $this->lang('Auteur') ?></dt><dd><?php echo utf8_htmlentities($theme->author) ?></dd></div>
					<div><dt><?php echo $this->lang('Licence') ?></dt><dd><?php echo utf8_htmlentities($theme->license) ?></dd></div>
				</dl>
			</div>
		</section>

		<section class="theme-customizer-pane" data-tab="background" hidden>
			<header class="theme-customizer-heading">
				<?php echo icon('far fa-image') ?>
				<h2><?php echo $this->lang('En-tête du site') ?></h2>
			</header>
			<?php echo $form_background ?>
		</section>

		<section class="theme-customizer-pane" data-tab="colors" hidden>
			<header class="theme-customizer-heading">
				<?php echo icon('fas fa-palette') ?>
				<h2><?php echo $this->lang('Couleurs') ?></h2>
			</header>
			<?php echo $form_colors ?>
		</section>
	</div>
</div>
