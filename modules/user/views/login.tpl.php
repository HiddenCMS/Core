<section class="user-auth-page">
	<div class="user-auth-box">
		<header class="user-auth-header">
			<span class="user-auth-icon"><?php echo icon('fas fa-user') ?></span>
			<div>
				<h1><?php echo $this->lang('Se connecter') ?></h1>
				<p><?php echo $this->lang('Accédez à votre espace personnel.') ?></p>
			</div>
		</header>

		<div class="user-auth-form">
			<?php echo $form ?>
		</div>

		<nav class="user-auth-links" aria-label="<?php echo $this->lang('Aide à la connexion') ?>">
			<a href="<?php echo url('user/lost-password') ?>"><?php echo $this->lang('Mot de passe oublié ?') ?></a>
			<?php if ($this->config->registration_status): ?>
			<a href="<?php echo url('user/register') ?>"><?php echo $this->lang('Créer un compte') ?></a>
			<?php endif ?>
		</nav>

		<?php if (!$authenticators->empty()): ?>
		<div class="user-auth-separator"><span><?php echo $this->lang('Ou continuer avec') ?></span></div>
		<div class="user-auth-providers">
			<?php foreach ($authenticators as $authenticator): ?>
			<a href="<?php echo url('user/auth/'.url_title($authenticator->info()->name)) ?>" title="<?php echo utf8_htmlentities($authenticator->info()->title) ?>">
				<img src="<?php echo image('authenticators/'.$authenticator->info()->name.'.png') ?>" alt="" />
				<span><?php echo $authenticator->info()->title ?></span>
			</a>
			<?php endforeach ?>
		</div>
		<?php endif ?>
	</div>
</section>
