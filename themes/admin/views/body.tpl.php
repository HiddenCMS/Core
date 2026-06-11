<div class="wrapper">
	<nav id="sidebar" class="ui vertical inverted menu">
		<div class="sidebar-header">
			<a class="logo" href="<?php echo url('admin') ?>">
				<svg><use xlink:href="#logo"></use></svg>
				<span class="ui tiny primary label">HiddenCMS</span>
			</a>
		</div>
		<?php echo $this->widget('navigation')->output('vertical', $this->__caller->data->get('sidebar')) ?>
	</nav>
	<div class="admin-content">
		<nav id="topbar" class="ui top attached menu">
			<button type="button" id="sidebarCollapse" class="ui icon button">
				<?php echo icon('fas fa-bars') ?>
			</button>
			<div class="right menu">
				<a class="item" href="<?php echo url('user') ?>"><?php echo $this->user->username ?></a>
				<a class="item" href="<?php echo url('user/logout') ?>"><?php echo icon('fas fa-times') ?> Se déconnecter</a>
				<a class="item" href="<?php echo url() ?>"><?php echo icon('fas fa-home') ?> Retourner sur le site</a>
			</div>
		</nav>

		<?php if (!($error = $this->output->error())): ?>
			<?php
				$module        = $this->output->module();
				$module_name   = $module->info()->name;
				$module_method = $this->output->data->get('module', 'method');

				$actions = $this->array($this->output->data->get('module', 'actions'))
								->append_if($module_method == 'index' && $module->get_permissions('default') && $this->module('access')->is_authorized(), $this->button('Permissions', 'fas fa-unlock-alt', 'success')->outline()->modal_ajax('admin/ajax/access/edit/'.$module_name.'/0-default'))
								->append_if(isset($module->info()->settings) && $this->module('addons')->is_authorized(), $this->button('Configuration', 'fas fa-wrench', 'warning')->outline()->modal_ajax('admin/addons/settings/'.$module->__addon->id.'/'.$module_name))
								->append_if(($help_controller = @$module->controller('admin_help')) && $help_controller->has_method($module_method), $this->button('Aide', 'far fa-life-ring', 'info')->outline()->modal_ajax('admin/addons/help/'.$module->__addon->id.'/'.$module_name.'/'.$module_method));
			?>
			<section id="page-header" class="ui clearing segment">
				<div class="header">
					<?php echo $this->label($this->output->data->get('module', 'title'), $this->output->data->get('module', 'icon')) ?>
					<?php if ($subtitle = $this->output->data->get('module', 'subtitle')): ?>
						<small class="subtitle"><?php echo $subtitle ?></small>
					<?php endif ?>
				</div>
				<?php if (!$actions->empty()): ?>
					<div class="actions">
						<?php echo $actions ?>
					</div>
				<?php endif ?>
			</section>

			<div class="module module-admin module-<?php echo $module->info()->name ?>"><?php echo $module ?></div>
		<?php else: ?>
			<div class="module module-admin module-error"><?php echo $error ?></div>
		<?php endif ?>

		<footer class="footer">
			<span class="muted"><?php echo $this->lang('Propulsé par').' HiddenCMS' ?></span>
			<ul class="project-links">
				<?php
				foreach ([
					[$this->lang('Projet'), 'https://github.com/HiddenCMS/Core']
				] as list($title, $url)): ?>
					<li>
						<a href="<?php echo $url ?>" target="_blank"><?php echo $title ?></a>
					</li>
				<?php endforeach ?>
			</ul>
		</footer>
	</div>
</div>
<?php echo $this->view('theme/logo') ?>
