<div class="hb-admin">
	<aside id="sidebar" class="hb-sidebar">
		<?php
			$username = (string)$this->user->username;
			$initial  = strtoupper(substr($username, 0, 1));
			$role     = $this->user->admin ? 'Administrateur' : 'Utilisateur';
			$version  = defined('HIDDENCMS_VERSION') ? HIDDENCMS_VERSION : 'n/a';
			$root     = dirname(__DIR__, 3);
			$updated  = @filemtime($root.'/.git/logs/HEAD') ?: @filemtime($root.'/index.php');
			$updated  = $updated ? date('d/m/Y H:i', $updated) : 'n/a';
			$changelog_url = 'https://github.com/HiddenCMS/Core/commits/main';
			$profile_url = $this->user->admin ? url('admin/user/user/update/'.$this->user->url()) : url('user');
		?>
		<div class="hb-sidebar-header">
			<a class="hb-logo" href="<?php echo url('admin') ?>">
				<span class="hb-logo-mark">HB</span>
				<span class="hb-logo-title">HiddenCMS</span>
			</a>
			<button type="button" id="sidebarClose" class="hb-sidebar-close" aria-label="Close sidebar" onclick="document.getElementById('sidebar').classList.remove('active');document.body.classList.remove('hb-no-scroll');">
				<?php echo icon('fas fa-times') ?>
			</button>
		</div>

		<?php echo $this->widget('navigation')->output('vertical', $this->__caller->data->get('sidebar')) ?>

		<div class="hb-sidebar-bottom">
			<a class="hb-sidebar-profile" href="<?php echo $profile_url ?>">
				<span class="hb-sidebar-avatar"><?php echo $initial ?: '?' ?></span>
				<div class="hb-sidebar-profile-text">
					<div class="hb-sidebar-username"><?php echo $username ?></div>
					<div class="hb-sidebar-role"><?php echo $role ?></div>
				</div>
				<span class="hb-sidebar-profile-arrow"><?php echo icon('fas fa-chevron-right') ?></span>
			</a>
			<div class="hb-sidebar-system">
				<div class="hb-sidebar-system-row">
					<span>HiddenCMS</span>
					<strong><?php echo $version ?></strong>
				</div>
				<div class="hb-sidebar-system-row">
					<span>Dernière maj</span>
					<strong><?php echo $updated ?></strong>
				</div>
					<a class="hb-btn hb-btn-secondary hb-btn-block hb-sidebar-changelog" href="<?php echo $changelog_url ?>" target="_blank" rel="noopener noreferrer"><?php echo icon('fas fa-scroll') ?> Changelog</a>
				<a class="hb-btn hb-btn-danger hb-btn-block hb-sidebar-logout" href="<?php echo url('user/logout') ?>"><?php echo icon('fas fa-sign-out-alt') ?> Déconnexion</a>
			</div>
		</div>
	</aside>
	<button type="button" id="sidebarOverlay" class="hb-sidebar-overlay" aria-label="Close sidebar" onclick="document.getElementById('sidebar').classList.remove('active');document.body.classList.remove('hb-no-scroll');"></button>

	<main class="hb-content">
		<header class="hb-topbar">
			<button type="button" id="sidebarCollapse" class="hb-icon-btn" aria-label="Toggle sidebar">
				<?php echo icon('fas fa-bars') ?>
			</button>
			<div class="hb-topbar-right">
					<button type="button" id="themeToggle" class="hb-btn hb-btn-secondary hb-topbar-theme" aria-label="Basculer le thème" aria-pressed="false">
					<span class="hb-topbar-theme-icon"><?php echo icon('far fa-lightbulb') ?></span>
				</button>
					<a class="hb-btn hb-btn-secondary hb-topbar-site" href="<?php echo url() ?>" data-tooltip="<?php echo $this->lang('Retourner sur le site') ?>" aria-label="<?php echo $this->lang('Retourner sur le site') ?>"><?php echo icon('fas fa-external-link-alt') ?></a>
			</div>
		</header>

		<?php if (!($error = $this->output->error())): ?>
			<?php
				$module        = $this->output->module();
				$module_name   = $module->info()->name;
				$module_method = $this->output->data->get('module', 'method');

				$actions = $this->array($this->output->data->get('module', 'actions'))
								->append_if($module_method == 'index' && $module->get_permissions('default') && $this->module('access')->is_authorized(), $this->button('Permissions', 'fas fa-unlock-alt', 'success', 'admin/access/edit/'.$module_name)->class('hb-btn-outline'))
								->append_if(isset($module->info()->settings) && $this->module('addons')->is_authorized(), $this->button('Configuration', 'fas fa-wrench', 'warning')->class('hb-btn-outline')->modal_ajax('admin/addons/settings/'.$module->__addon->id.'/'.$module_name))
								->append_if(($help_controller = @$module->controller('admin_help')) && $help_controller->has_method($module_method), $this->button('Aide', 'far fa-life-ring', 'info')->class('hb-btn-outline')->modal_ajax('admin/addons/help/'.$module->__addon->id.'/'.$module_name.'/'.$module_method));
			?>

			<section class="hb-page-header">
				<div class="hb-page-title">
					<?php echo $this->label($this->output->data->get('module', 'title'), $this->output->data->get('module', 'icon')) ?>
					<?php if ($subtitle = $this->output->data->get('module', 'subtitle')): ?>
						<small class="hb-page-subtitle"><?php echo $subtitle ?></small>
					<?php endif ?>
				</div>

				<?php if (!$actions->empty()): ?>
					<div class="hb-page-actions">
						<?php echo $actions ?>
					</div>
				<?php endif ?>
			</section>

			<section class="hb-page-content module module-admin module-<?php echo $module->info()->name ?>">
				<?php echo $module ?>
			</section>
		<?php else: ?>
			<section class="hb-page-content module module-admin module-error">
				<?php echo $error ?>
			</section>
		<?php endif ?>

		<footer class="hb-footer">
			<div class="hb-footer-left"><?php echo $this->lang('Propulse par').' HiddenCMS' ?></div>
			<div class="hb-footer-right">
				<a href="https://github.com/HiddenCMS/Core" target="_blank"><?php echo $this->lang('Projet') ?></a>
			</div>
		</footer>
	</main>
</div>
<?php echo $this->view('theme/logo') ?>
