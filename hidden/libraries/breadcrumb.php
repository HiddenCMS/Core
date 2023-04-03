<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Hidden\Libraries;

use HD\Hidden\Library;

class Breadcrumb extends Library
{
	public function __invoke($title = '', $link = '', $icon = '')
	{
		$this->output->data->append('breadcrumb', [
			$title ?: $this->output->data->get('module', 'title'),
			$link  ?: $this->url->request,
			$icon  ?: $this->output->data->get('module', 'icon')
		]);

		return $this;
	}
}
