<div class="wrapper">
	<nav id="sidebar">
		<div class="sidebar-header">
			<a class="logo" href="<?php echo url('admin') ?>">
				<svg><use xlink:href="#logo"></use></svg>
				<span class="badge badge-primary">HiddenCMS</span>
			</a>
		</div>
		<?php echo $this->widget('navigation')->output('vertical', $this->__caller->data->get('sidebar')) ?>
	</nav>
	<div class="content">
		<nav class="navbar navbar-expand-lg navbar-user navbar-dark mb-0">
			<button type="button" id="sidebarCollapse" class="btn btn-primary">
				<?php echo icon('fas fa-bars') ?>
			</button>
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar_user" aria-controls="navbar_user" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbar_user">
				<ul class="navbar-nav ml-auto">
					<li class="nav-item active"><a class="nav-link btn btn-link" href="<?php echo url('user') ?>"><?php echo $this->user->username ?></a></li>
					<li class="nav-item"><a class="nav-link btn btn-link" href="<?php echo url('user/logout') ?>"><?php echo icon('fas fa-times') ?> Se déconnecter</a></li>
					<li class="nav-item nav-item-separator d-none d-lg-block">/</li>
					<li class="nav-item"><a class="nav-link btn btn-link" href="<?php echo url() ?>"><?php echo icon('fas fa-home') ?> Retourner sur le site</a></li>
				</ul>
			</div>
		</nav>
		<?php if (!($error = $this->output->error())): ?>
			<nav class="navbar navbar-expand-lg navbar-header navbar-light mb-4">
				<span class="navbar-brand">
					<?php echo $this->label($this->output->data->get('module', 'title'), $this->output->data->get('module', 'icon')) ?>
					<?php if ($subtitle = $this->output->data->get('module', 'subtitle')): ?>
						<small class="subtitle"><?php echo $subtitle ?></small>
					<?php endif ?>
				</span>
				<?php
					$module        = $this->output->module();
					$module_name   = $module->info()->name;
					$module_method = $this->output->data->get('module', 'method');

					$actions = $this->array($this->output->data->get('module', 'actions'))
									->append_if($module_method == 'index' && $module->get_permissions('default') && $this->module('access')->is_authorized(), $this->button('Permissions', 'fas fa-unlock-alt', 'success', 'admin/access/edit/'.$module_name)->outline())
									->append_if(isset($module->info()->settings) && $this->module('addons')->is_authorized(), $this->button('Configuration', 'fas fa-wrench', 'warning')->outline()->modal_ajax('admin/addons/settings/'.$module->__addon->id.'/'.$module_name))
									->append_if(($help_controller = @$module->controller('admin_help')) && $help_controller->has_method($module_method), $this->button('Aide', 'far fa-life-ring', 'info')->outline()->modal_ajax('admin/addons/help/'.$module->__addon->id.'/'.$module_name.'/'.$module_method));
				?>
				<?php if (!$actions->empty()): ?>
					<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar_header" aria-controls="navbar_header" aria-expanded="false" aria-label="Toggle navigation">
						<span class="navbar-toggler-icon"></span>
					</button>
					<div class="collapse navbar-collapse flex-row-reverse" id="navbar_header">
						<div class="actions">
							<?php echo $actions ?>
						</div>
					</div>
				<?php endif ?>
			</nav>
			<div class="module module-admin module-<?php echo $module->info()->name ?>"><?php echo $module ?></div>
		<?php else: ?>
			<div class="module module-admin module-error"><?php echo $error ?></div>
		<?php endif ?>
		<footer class="footer">
			<span class="text-muted"><?php echo $this->lang('Propulsé par').' HiddenCMS' ?></span>
			<ul class="mb-0 list-inline float-right">
				<?php
				foreach ([
					[$this->lang('Projet'), 'https://github.com/HiddenCMS/Core']
				] as list($title, $url)): ?>
					<li class="list-inline-item">
						<a href="<?php echo $url ?>" target="_blank"><?php echo $title ?></a>
					</li>
				<?php endforeach ?>
			</ul>
		</footer>
	</div>
</div>
<?php echo $this->view('theme/logo') ?>
