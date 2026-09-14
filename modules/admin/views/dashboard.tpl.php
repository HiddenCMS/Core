<?php $d = $dashboard; $escape = function($value){ return utf8_htmlentities(utf8_html_entity_decode((string)$value), ENT_QUOTES); }; $number = function($value){ return number_format($value, 0, ',', ' '); }; ?>
<section class="dashboard">
    <div class="dashboard-toolbar"><div><h2><?php echo $escape($this->config->name); ?></h2><p><?php echo $this->lang('Overview - last 30 days'); ?></p></div><div class="dashboard-actions">
        <?php if ($d['has_pages']): ?><a href="<?php echo url('admin/pages/add'); ?>" class="ui primary button"><?php echo icon('fas fa-plus'); ?> <?php echo $this->lang('Create a page'); ?></a><?php endif; ?>
        <a href="<?php echo url('admin/files'); ?>" class="ui button"><?php echo icon('fas fa-folder-open'); ?> <?php echo $this->lang('Media library'); ?></a>
        <a href="<?php echo url('admin/live-editor'); ?>" class="ui button"><?php echo icon('fas fa-edit'); ?> <?php echo $this->lang('Live editor'); ?></a>
    </div></div>
    <div class="dashboard-metrics">
        <?php if ($d['traffic']): foreach ([[$this->lang('Visits'), 'fas fa-walking', $d['traffic']['visits'], '#1696a5'], [$this->lang('Page views'), 'fas fa-eye', $d['traffic']['views'], '#4278c4']] as [$title, $icon, $value, $color]): ?>
        <a class="dashboard-metric" href="<?php echo url('admin/statistics?period=30'); ?>" style="--metric-color:<?php echo $color; ?>"><span><?php echo icon($icon).' '.$title; ?></span><strong><?php echo $number($value); ?></strong><small><?php echo $this->lang('Traffic measured after consent'); ?></small></a>
        <?php endforeach; endif; ?>
        <a class="dashboard-metric" href="<?php echo url('admin/user'); ?>" style="--metric-color:#4d9364"><span><?php echo icon('fas fa-user-plus'); ?> <?php echo $this->lang('New members'); ?></span><strong><?php echo $number($d['new_members']); ?></strong><small><?php echo $this->lang('Registrations over 30 days'); ?></small></a>
    </div>
    <div class="dashboard-columns"><div class="dashboard-main">
        <section class="dashboard-section"><div class="dashboard-heading"><h3><?php echo icon('fas fa-layer-group'); ?> <?php echo $this->lang('Recent content'); ?></h3><span><?php echo $this->lang('Latest additions by module'); ?></span></div>
            <?php foreach ($d['contents'] as $group): ?><div class="dashboard-content-group"><div class="dashboard-group-heading"><h4><?php echo icon($group['icon']).' '.$group['label']; ?></h4><a href="<?php echo url('admin/'.$group['module']); ?>"><?php echo $this->lang('View all'); ?> <?php echo icon('fas fa-arrow-right'); ?></a></div>
                <?php foreach ($group['rows'] as $row): ?><a class="dashboard-content-row" href="<?php echo url($row['url']); ?>"><span><?php echo $escape($row['title']); ?></span><span class="dashboard-badge <?php echo $row['published'] ? 'published' : 'draft'; ?>"><?php echo $row['published'] ? $this->lang('Published / active') : $this->lang('Unpublished / inactive'); ?></span><i class="fas fa-pen" aria-hidden="true"></i></a><?php endforeach; ?>
                <?php if (!$group['rows']): ?><p class="dashboard-empty"><?php echo $this->lang('No content yet.'); ?></p><?php endif; ?>
            </div><?php endforeach; ?>
            <?php if (!$d['contents']): ?><p class="dashboard-empty"><?php echo $this->lang('No active content modules.'); ?></p><?php endif; ?>
        </section>
    </div><aside class="dashboard-side">
        <section class="dashboard-section"><div class="dashboard-heading"><h3><?php echo icon('fas fa-clipboard-list'); ?> <?php echo $this->lang('To do'); ?></h3></div>
            <?php foreach ($d['tasks'] as $task): ?><a class="dashboard-task" href="<?php echo url($task['url']); ?>"><?php echo icon($task['icon']); ?><span><?php echo $task['title']; ?></span><strong><?php echo $number($task['count']); ?></strong></a><?php endforeach; ?>
            <?php if (!$d['tasks']): ?><p class="dashboard-clear"><?php echo icon('fas fa-check-circle'); ?> <?php echo $this->lang('Nothing pending.'); ?></p><?php endif; ?>
            <?php foreach ($d['requests'] as $request): ?><a class="dashboard-request" href="<?php echo url('admin/user/user/update/'.$request['user_id'].'/'.url_title($request['username'])); ?>"><span><?php echo $escape($request['username']); ?></span><small><?php echo $this->lang('Erasure scheduled for %s', date('Y-m-d', strtotime($request['execute_after']))); ?></small></a><?php endforeach; ?>
        </section>
        <section class="dashboard-section"><div class="dashboard-heading"><h3><?php echo icon('fas fa-heartbeat'); ?> <?php echo $this->lang('Site status'); ?></h3></div>
            <a class="dashboard-status" href="<?php echo url('admin/settings/maintenance'); ?>"><span><?php echo icon('fas fa-power-off'); ?> <?php echo $this->lang('Availability'); ?></span><span class="dashboard-badge <?php echo $d['maintenance'] ? 'draft' : 'published'; ?>"><?php echo $d['maintenance'] ? $this->lang('Maintenance') : $this->lang('Open'); ?></span></a>
            <a class="dashboard-status" href="<?php echo url('admin/settings/smtp'); ?>"><span><?php echo icon('fas fa-envelope'); ?> <?php echo $this->lang('Email delivery'); ?></span><span class="dashboard-badge <?php echo $d['smtp'] && !$d['smtp_host'] ? 'draft' : 'neutral'; ?>"><?php echo $d['smtp'] ? ($d['smtp_host'] ? $this->lang('SMTP configured') : $this->lang('SMTP incomplete')) : $this->lang('PHP mail'); ?></span></a>
            <a class="dashboard-status" href="<?php echo url('admin/settings/updates'); ?>"><span><?php echo icon('fas fa-cloud-download-alt'); ?> <?php echo $this->lang('Updates'); ?></span><span class="dashboard-badge <?php echo $d['updates'] ? 'draft' : 'neutral'; ?>"><?php echo $d['updates'] === NULL ? $this->lang('Check required') : ($d['updates'] ? $this->lang('%s available', $number($d['updates'])) : $this->lang('None found')); ?></span></a>
            <p class="dashboard-note">Core <?php echo $escape(HIDDENCMS_VERSION); ?><?php if ($d['checked_at']): ?><br><?php echo $this->lang('Last checked: %s', date('Y-m-d H:i', strtotime($d['checked_at']))); ?><?php endif; ?></p>
        </section>
    </aside></div>
</section>
