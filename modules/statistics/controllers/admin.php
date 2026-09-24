<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\Modules\Statistics\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Admin extends Controller_Module
{
	public function index()
	{
		if (!$this->is_authorized('view_statistics')) return $this->error->unauthorized();
		$model = $this->model('overview');
		$days = $model->period($_GET['period'] ?? $this->session('statistics', 'overview_period'));
		$this->session->set('statistics', 'overview_period', $days);
		$snapshot = $model->snapshot($days);
		$traffic = $this->model('traffic')->report($days);
		if (($_GET['export'] ?? '') === 'csv') {
			$daily = array_column($traffic['daily'], NULL, 'day');
			foreach (['views' => 'Page views', 'visits' => 'Visits (sessions per day)'] as $key => $label) {
				$points = []; foreach ($snapshot['series'][0]['data'] as $point) $points[] = [$point[0], (int)($daily[$point[0]][$key] ?? 0)];
				$snapshot['series'][] = ['name' => (string)$this->lang($label), 'data' => $points];
			}
			header('Content-Type: text/csv; charset=UTF-8');
			header('Content-Disposition: attachment; filename="statistics-'.$days.'-days.csv"');
			header('Cache-Control: private, no-store');
			echo $model->csv($snapshot); exit;
		}
		$this->css('overview')->js('highstock')->js('overview');
		$periods = [];
		foreach (\HB\Modules\Statistics\Models\Overview::PERIODS as $period => $label) $periods[$period] = (string)$this->lang($label);
		return $this->view('overview', ['snapshot' => $snapshot, 'traffic' => $traffic, 'periods' => $periods]);
	}
}


