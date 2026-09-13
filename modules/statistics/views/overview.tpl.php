<?php $s = $snapshot; $number = function($value){ return number_format($value, 0, ',', ' '); }; ?>
<section class="statistics-overview">
    <div class="statistics-toolbar">
        <div><h2>Activité du site</h2><p><?php echo $s['start'].' — '.$s['end']; ?></p></div>
        <form method="get" class="statistics-period">
            <label for="statistics-period">Période</label>
            <select id="statistics-period" name="period">
                <?php foreach ($periods as $days => $label): ?><option value="<?php echo $days; ?>" <?php echo $days === $s['days'] ? 'selected' : ''; ?>><?php echo $label; ?></option><?php endforeach; ?>
            </select>
            <button class="ui primary button" type="submit"><?php echo icon('fas fa-sync-alt'); ?> Actualiser</button>
        </form>
        <a class="ui button" href="<?php echo url('admin/statistics?period='.$s['days'].'&export=csv'); ?>"><?php echo icon('fas fa-download'); ?> Export CSV</a>
    </div>
    <div class="statistics-metrics">
        <?php foreach ([['Visites', 'fas fa-walking', $traffic['visits']], ['Pages vues', 'fas fa-eye', $traffic['views']]] as [$label, $icon, $value]): ?>
        <article class="statistics-metric" style="--metric-color:#86629b"><div class="statistics-metric-title"><?php echo icon($icon); ?><span><?php echo $label; ?></span></div><strong><?php echo $number($value); ?></strong><div class="statistics-comparison">Mesure locale avec accord du visiteur</div></article>
        <?php endforeach; ?>
        <?php foreach ($s['metrics'] as $metric): ?>
        <article class="statistics-metric" style="--metric-color:<?php echo $metric['color']; ?>">
            <div class="statistics-metric-title"><?php echo icon($metric['icon']); ?><span><?php echo $metric['label']; ?></span></div>
            <strong><?php echo $number($metric['value']); ?></strong>
            <div class="statistics-comparison">
                <?php if ($metric['change'] !== NULL): ?><span class="<?php echo $metric['change'] > 0 ? 'positive' : ($metric['change'] < 0 ? 'negative' : ''); ?>"><?php echo ($metric['change'] > 0 ? '+' : '').$metric['change']; ?> %</span><?php else: ?><span>—</span><?php endif; ?>
                <span><?php echo $number($metric['previous']); ?> sur la période précédente</span>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <div class="statistics-tabs" role="tablist" aria-label="Statistiques">
        <button id="statistics-traffic-tab" type="button" role="tab" aria-selected="false" aria-controls="statistics-traffic" tabindex="-1"><?php echo icon('fas fa-eye'); ?> Fréquentation</button>
        <button id="statistics-activity-tab" type="button" role="tab" aria-selected="true" aria-controls="statistics-activity"><?php echo icon('fas fa-chart-line'); ?> Activité</button>
        <button id="statistics-content-tab" type="button" role="tab" aria-selected="false" aria-controls="statistics-content" tabindex="-1"><?php echo icon('fas fa-layer-group'); ?> Contenus</button>
    </div>
    <div id="statistics-traffic" role="tabpanel" aria-labelledby="statistics-traffic-tab" hidden>
        <div class="statistics-section-heading"><h3>Fréquentation quotidienne</h3><span><?php echo $s['days']; ?> jours</span></div>
        <?php $daily = array_column($traffic['daily'], NULL, 'day'); $traffic_series = [];
        foreach (['views' => ['Pages vues', '#1696a5'], 'visits' => ['Visites', '#86629b']] as $key => [$label, $color]) {
            $points = []; foreach ($s['series'][0]['data'] as $point) $points[] = [$point[0], (int)($daily[$point[0]][$key] ?? 0)];
            $traffic_series[] = ['name' => $label, 'color' => $color, 'data' => $points];
        } ?>
        <div class="statistics-chart" data-series="<?php echo utf8_htmlentities(json_encode($traffic_series)); ?>"></div>
        <p class="statistics-note">Une visite correspond à une session de navigateur distincte par jour, pas à une personne unique. Seules les pages consultées après accord sont mesurées. Administration, espaces utilisateurs, fichiers et robots identifiés sont exclus ; les paramètres des URL ne sont pas enregistrés. Ces compteurs commencent à l’activation, sans historique rétroactif.</p>
        <?php if (!$traffic['ready']): ?><p class="statistics-note">La migration des tables de fréquentation doit être appliquée.</p><?php endif; ?>
        <div class="statistics-section-heading"><h3>Pages les plus vues</h3><span>15 premières</span></div>
        <div class="statistics-table-scroll"><table class="statistics-table"><thead><tr><th>Page</th><th>Vues</th><th>Visites</th></tr></thead><tbody>
        <?php foreach ($traffic['pages'] as $page): ?><tr><td><a href="<?php echo utf8_htmlentities($page['path'], ENT_QUOTES); ?>" target="_blank" rel="noopener"><?php echo utf8_htmlentities($page['path']); ?></a></td><td><?php echo $number($page['views']); ?></td><td><?php echo $number($page['visits']); ?></td></tr><?php endforeach; ?>
        <?php if (!$traffic['pages']): ?><tr><td colspan="3">Aucune consultation mesurée sur cette période.</td></tr><?php endif; ?>
        </tbody></table></div>
    </div>
    <div id="statistics-activity" role="tabpanel" aria-labelledby="statistics-activity-tab">
        <div class="statistics-section-heading"><h3>Évolution quotidienne</h3><span><?php echo $s['days']; ?> jours</span></div>
        <?php $visitors = $traffic_series[1]; $visitors['name'] = 'Visiteurs (sessions par jour)'; $activity_series = array_merge($s['series'], [$visitors]); ?>
        <div class="statistics-chart" data-series="<?php echo utf8_htmlentities(json_encode($activity_series)); ?>"></div>
        <p class="statistics-note">Les connexions comptent les membres distincts sur la période ; la courbe les compte par jour. Elles ne représentent pas les visites du site. Les données disponibles dépendent des durées de conservation configurées.</p>
        <p class="statistics-note">La courbe des visiteurs compte les sessions de navigateur distinctes par jour, après accord à la mesure d’audience. Une même personne peut utiliser plusieurs navigateurs ou revenir plusieurs jours.</p>
        <details class="statistics-detail"><summary>Données quotidiennes</summary><div class="statistics-table-scroll"><table class="statistics-table"><thead><tr><th>Date</th><?php foreach ($activity_series as $series): ?><th><?php echo $series['name']; ?></th><?php endforeach; ?></tr></thead><tbody>
            <?php for ($i = $s['days'] - 1; $i >= 0; $i--): ?><tr><td><?php echo $s['series'][0]['data'][$i][0]; ?></td><?php foreach ($activity_series as $series): ?><td><?php echo $number($series['data'][$i][1]); ?></td><?php endforeach; ?></tr><?php endfor; ?>
        </tbody></table></div></details>
    </div>
    <div id="statistics-content" role="tabpanel" aria-labelledby="statistics-content-tab" hidden>
        <div class="statistics-section-heading"><h3>Inventaire des contenus</h3><span>État actuel</span></div>
        <div class="statistics-table-scroll"><table class="statistics-table"><thead><tr><th>Module</th><th>Total</th><th>Publiés / actifs</th><th>Non publiés / inactifs</th><th>Publication</th></tr></thead><tbody>
            <?php foreach ($s['inventory'] as $item): ?><tr>
                <td><a href="<?php echo url('admin/'.$item['module']); ?>"><?php echo icon($item['icon']); ?> <?php echo $item['label']; ?></a></td>
                <td><?php echo $number($item['total']); ?></td><td><?php echo $number($item['published']); ?></td><td><?php echo $number($item['total'] - $item['published']); ?></td>
                <td><progress value="<?php echo $item['published']; ?>" max="<?php echo max(1, $item['total']); ?>" aria-label="Publication des <?php echo $item['label']; ?>"></progress><span><?php echo $item['total'] ? round($item['published'] / $item['total'] * 100) : 0; ?> %</span></td>
            </tr><?php endforeach; ?>
            <?php if (!$s['inventory']): ?><tr><td colspan="5">Aucun module de contenu actif.</td></tr><?php endif; ?>
        </tbody></table></div>
        <div class="statistics-totals"><span><?php echo icon('fas fa-users'); ?> <strong><?php echo $number($s['members']); ?></strong> comptes membres</span><span><?php echo icon('fas fa-photo-video'); ?> <strong><?php echo $number($s['media']); ?></strong> fichiers dans la médiathèque</span></div>
    </div>
</section>
