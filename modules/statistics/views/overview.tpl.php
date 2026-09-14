<?php $s = $snapshot; $number = function($value){ return number_format($value, 0, ',', ' '); }; ?>
<section class="statistics-overview">
    <div class="statistics-toolbar">
        <div><h2><?php echo $this->lang('Site activity'); ?></h2><p><?php echo $s['start'].' — '.$s['end']; ?></p></div>
        <form method="get" class="statistics-period">
            <label for="statistics-period"><?php echo $this->lang('Period'); ?></label>
            <select id="statistics-period" name="period">
                <?php foreach ($periods as $days => $label): ?><option value="<?php echo $days; ?>" <?php echo $days === $s['days'] ? 'selected' : ''; ?>><?php echo $label; ?></option><?php endforeach; ?>
            </select>
            <button class="ui primary button" type="submit"><?php echo icon('fas fa-sync-alt'); ?> <?php echo $this->lang('Refresh'); ?></button>
        </form>
        <a class="ui button" href="<?php echo url('admin/statistics?period='.$s['days'].'&export=csv'); ?>"><?php echo icon('fas fa-download'); ?> <?php echo $this->lang('Export CSV'); ?></a>
    </div>
    <div class="statistics-metrics">
        <?php foreach ([[$this->lang('Visits'), 'fas fa-walking', $traffic['visits']], [$this->lang('Page views'), 'fas fa-eye', $traffic['views']]] as [$label, $icon, $value]): ?>
        <article class="statistics-metric" style="--metric-color:#86629b"><div class="statistics-metric-title"><?php echo icon($icon); ?><span><?php echo $label; ?></span></div><strong><?php echo $number($value); ?></strong><div class="statistics-comparison"><?php echo $this->lang('Local measurement with visitor consent'); ?></div></article>
        <?php endforeach; ?>
        <?php foreach ($s['metrics'] as $metric): ?>
        <article class="statistics-metric" style="--metric-color:<?php echo $metric['color']; ?>">
            <div class="statistics-metric-title"><?php echo icon($metric['icon']); ?><span><?php echo $metric['label']; ?></span></div>
            <strong><?php echo $number($metric['value']); ?></strong>
            <div class="statistics-comparison">
                <?php if ($metric['change'] !== NULL): ?><span class="<?php echo $metric['change'] > 0 ? 'positive' : ($metric['change'] < 0 ? 'negative' : ''); ?>"><?php echo ($metric['change'] > 0 ? '+' : '').$metric['change']; ?> %</span><?php else: ?><span>—</span><?php endif; ?>
                <span><?php echo $this->lang('%s in the previous period', $number($metric['previous'])); ?></span>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <div class="statistics-tabs" role="tablist" aria-label="<?php echo $this->lang('Statistics'); ?>">
        <button id="statistics-traffic-tab" type="button" role="tab" aria-selected="false" aria-controls="statistics-traffic" tabindex="-1"><?php echo icon('fas fa-eye'); ?> <?php echo $this->lang('Traffic'); ?></button>
        <button id="statistics-activity-tab" type="button" role="tab" aria-selected="true" aria-controls="statistics-activity"><?php echo icon('fas fa-chart-line'); ?> <?php echo $this->lang('Activity'); ?></button>
        <button id="statistics-content-tab" type="button" role="tab" aria-selected="false" aria-controls="statistics-content" tabindex="-1"><?php echo icon('fas fa-layer-group'); ?> <?php echo $this->lang('Content'); ?></button>
    </div>
    <div id="statistics-traffic" role="tabpanel" aria-labelledby="statistics-traffic-tab" hidden>
        <div class="statistics-section-heading"><h3><?php echo $this->lang('Daily traffic'); ?></h3><span><?php echo $this->lang('%d days', $s['days']); ?></span></div>
        <?php $daily = array_column($traffic['daily'], NULL, 'day'); $traffic_series = [];
        foreach (['views' => [$this->lang('Page views'), '#1696a5'], 'visits' => [$this->lang('Visits'), '#86629b']] as $key => [$label, $color]) {
            $points = []; foreach ($s['series'][0]['data'] as $point) $points[] = [$point[0], (int)($daily[$point[0]][$key] ?? 0)];
            $traffic_series[] = ['name' => (string)$label, 'color' => $color, 'data' => $points];
        } ?>
        <div class="statistics-chart" data-unavailable="<?php echo utf8_htmlentities((string)$this->lang('Chart unavailable. The data is still accessible in the table below.'), ENT_QUOTES); ?>" data-series="<?php echo utf8_htmlentities(json_encode($traffic_series)); ?>"></div>
        <p class="statistics-note"><?php echo $this->lang('A visit represents a distinct browser session per day, not a unique person. Only pages viewed after consent are measured. Administration, user areas, files and identified bots are excluded; URL parameters are not stored. These counters start when enabled, with no retrospective history.'); ?></p>
        <?php if (!$traffic['ready']): ?><p class="statistics-note"><?php echo $this->lang('The traffic table migration must be applied.'); ?></p><?php endif; ?>
        <div class="statistics-section-heading"><h3><?php echo $this->lang('Most viewed pages'); ?></h3><span><?php echo $this->lang('Top 15'); ?></span></div>
        <div class="statistics-table-scroll"><table class="statistics-table"><thead><tr><th>Page</th><th><?php echo $this->lang('Views'); ?></th><th><?php echo $this->lang('Visits'); ?></th></tr></thead><tbody>
        <?php foreach ($traffic['pages'] as $page): ?><tr><td><a href="<?php echo utf8_htmlentities($page['path'], ENT_QUOTES); ?>" target="_blank" rel="noopener"><?php echo utf8_htmlentities($page['path']); ?></a></td><td><?php echo $number($page['views']); ?></td><td><?php echo $number($page['visits']); ?></td></tr><?php endforeach; ?>
        <?php if (!$traffic['pages']): ?><tr><td colspan="3"><?php echo $this->lang('No page views measured during this period.'); ?></td></tr><?php endif; ?>
        </tbody></table></div>
    </div>
    <div id="statistics-activity" role="tabpanel" aria-labelledby="statistics-activity-tab">
        <div class="statistics-section-heading"><h3><?php echo $this->lang('Daily activity'); ?></h3><span><?php echo $this->lang('%d days', $s['days']); ?></span></div>
        <?php $visitors = $traffic_series[1]; $visitors['name'] = (string)$this->lang('Visitors (sessions per day)'); $activity_series = array_merge($s['series'], [$visitors]); ?>
        <div class="statistics-chart" data-unavailable="<?php echo utf8_htmlentities((string)$this->lang('Chart unavailable. The data is still accessible in the table below.'), ENT_QUOTES); ?>" data-series="<?php echo utf8_htmlentities(json_encode($activity_series)); ?>"></div>
        <p class="statistics-note"><?php echo $this->lang('Sign-ins count distinct members over the period; the chart counts them per day. They do not represent site visits. Available data depends on the configured retention periods.'); ?></p>
        <p class="statistics-note"><?php echo $this->lang('The visitor chart counts distinct browser sessions per day after analytics consent. The same person may use several browsers or return on multiple days.'); ?></p>
        <details class="statistics-detail"><summary><?php echo $this->lang('Daily data'); ?></summary><div class="statistics-table-scroll"><table class="statistics-table"><thead><tr><th>Date</th><?php foreach ($activity_series as $series): ?><th><?php echo $series['name']; ?></th><?php endforeach; ?></tr></thead><tbody>
            <?php for ($i = $s['days'] - 1; $i >= 0; $i--): ?><tr><td><?php echo $s['series'][0]['data'][$i][0]; ?></td><?php foreach ($activity_series as $series): ?><td><?php echo $number($series['data'][$i][1]); ?></td><?php endforeach; ?></tr><?php endfor; ?>
        </tbody></table></div></details>
    </div>
    <div id="statistics-content" role="tabpanel" aria-labelledby="statistics-content-tab" hidden>
        <div class="statistics-section-heading"><h3><?php echo $this->lang('Content inventory'); ?></h3><span><?php echo $this->lang('Current status'); ?></span></div>
        <div class="statistics-table-scroll"><table class="statistics-table"><thead><tr><th>Module</th><th>Total</th><th><?php echo $this->lang('Published / active'); ?></th><th><?php echo $this->lang('Unpublished / inactive'); ?></th><th>Publication</th></tr></thead><tbody>
            <?php foreach ($s['inventory'] as $item): ?><tr>
                <td><a href="<?php echo url('admin/'.$item['module']); ?>"><?php echo icon($item['icon']); ?> <?php echo $item['label']; ?></a></td>
                <td><?php echo $number($item['total']); ?></td><td><?php echo $number($item['published']); ?></td><td><?php echo $number($item['total'] - $item['published']); ?></td>
                <td><progress value="<?php echo $item['published']; ?>" max="<?php echo max(1, $item['total']); ?>" aria-label="<?php echo utf8_htmlentities((string)$this->lang('Publication of %s', $item['label']), ENT_QUOTES); ?>"></progress><span><?php echo $item['total'] ? round($item['published'] / $item['total'] * 100) : 0; ?> %</span></td>
            </tr><?php endforeach; ?>
            <?php if (!$s['inventory']): ?><tr><td colspan="5"><?php echo $this->lang('No active content modules.'); ?></td></tr><?php endif; ?>
        </tbody></table></div>
        <div class="statistics-totals"><span><?php echo icon('fas fa-users'); ?> <strong><?php echo $number($s['members']); ?></strong> <?php echo $this->lang('member accounts'); ?></span><span><?php echo icon('fas fa-photo-video'); ?> <strong><?php echo $number($s['media']); ?></strong> <?php echo $this->lang('files in the media library'); ?></span></div>
    </div>
</section>
