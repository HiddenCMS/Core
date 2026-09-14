<section class="user-auth-page">
	<?php if (!empty($this->config->login_logo) && !empty($this->config->team_logo) && ($logo = HB()->model2('file', $this->config->team_logo)) && $logo()): ?>
	<a class="user-auth-logo" href="<?php echo url() ?>"><img src="<?php echo utf8_htmlentities($logo->path(), ENT_QUOTES) ?>" alt="<?php echo utf8_htmlentities(utf8_html_entity_decode($this->config->name), ENT_QUOTES) ?>"></a>
	<?php endif ?>
	<div class="user-auth-box">
		<header class="user-auth-header">
			<span class="user-auth-icon"><?php echo icon('fas fa-user') ?></span>
			<div>
				<h1><?php echo $this->lang('Sign in') ?></h1>
				<p><?php echo $this->lang('Access your personal account.') ?></p>
			</div>
		</header>

		<div class="user-auth-form">
			<?php echo $form ?>
		</div>

		<nav class="user-auth-links" aria-label="<?php echo $this->lang('Sign-in help') ?>">
			<a href="<?php echo url() ?>"><?php echo icon('fas fa-arrow-left').' '.$this->lang('Back to home') ?></a>
			<a href="<?php echo url('user/lost-password') ?>"><?php echo $this->lang('Forgot your password?') ?></a>
			<?php if ($this->config->registration_status): ?>
			<a href="<?php echo url('user/register') ?>"><?php echo $this->lang('Create an account') ?></a>
			<?php endif ?>
		</nav>

		<?php if (!$authenticators->empty()): ?>
		<div class="user-auth-separator"><span><?php echo $this->lang('Or continue with') ?></span></div>
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
