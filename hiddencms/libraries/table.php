<?php
/**
 * https://neofr.ag
 * @author: MichaÃ«l BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries;

use HB\HiddenCMS\Library;

class Table extends Library
{
	static protected $_table;

	private $_ajax          = FALSE;
	private $_pagination    = TRUE;
	private $_columns       = [];
	private $_data          = [];
	private $_sortings      = [];
	private $_preprocessing = [];
	private $_no_data       = '';
	private $_words         = [];

	public function __invoke()
	{
		if (!static::$_table)
		{
			static::$_table = $this;
			$this->id = $this->__id();
		}

		return static::$_table;
	}

	function __toString()
	{
		return (string)$this->_table;
	}

	public function add_column($title, $content, $size = NULL, $search = NULL, $sort = NULL, $align = 'left')
	{
		$this->_columns[] = [
			'title'   => $title,
			'content' => $content,
			'size'    => $size,
			'search'  => $search,
			'sort'    => $sort,
			'align'   => $align
		];

		return $this;
	}

	public function add_columns($columns)
	{
		$this->_columns = array_merge($this->_columns, $columns);
		return $this;
	}

	public function sort_by($column_id, $order = SORT_ASC, $type = SORT_REGULAR)
	{
		if (is_integer($column_id) && in_array($order, [SORT_ASC, SORT_DESC, -1]) && in_array($type, [SORT_REGULAR, SORT_NUMERIC, SORT_STRING]))
		{
			if (isset($this->_sortings[$column_id - 1]))
			{
				unset($this->_sortings[$column_id - 1]);
			}

			$this->_sortings[$column_id - 1] = [$order, $type];
		}

		return $this;
	}

	public function data($data)
	{
		$this->_data = $data;
		return $this;
	}

	public function no_data($no_data)
	{
		$this->_no_data = $no_data;
		return $this;
	}

	public function pagination($pagination = TRUE)
	{
		$this->_pagination = $pagination;
		return $this;
	}

	public function preprocessing($callback)
	{
		$this->_preprocessing = $callback;
		return $this;
	}

	public function display()
	{
		HB()->css('table')->js('table');

		$output = '';
		$search = trim((string)post('search'));

		if (post('table_id'))
		{
			if (post('table_id') == $this->id)
			{
				$this->_ajax = TRUE;
			}
			else
			{
				$this->save();
				return;
			}
		}

		if (($session_sort = $this->session('table', $this->id, 'sort')) !== NULL)
		{
			foreach ($session_sort as $session)
			{
				list($column_id, $order) = $session;
				$this->sort_by($column_id, $order);
			}
		}

		if ($this->_pagination && !empty($this->output->module()->pagination) && ($items_per_page = $this->session('table', $this->id, 'items_per_page')) !== NULL)
		{
			$this->output->module()->pagination->set_items_per_page($items_per_page);
		}

		if (($sort = post('sort')) !== NULL && $this->_ajax)
		{
			list($column_id, $order) = json_decode($sort);

			if (in_array($order, ['asc', 'desc', 'none']))
			{
				if ($order == 'asc')
				{
					$order = SORT_ASC;
				}
				else if ($order == 'desc')
				{
					$order = SORT_DESC;
				}
				else if ($order == 'none')
				{
					$order = -1;
				}

				$added = FALSE;

				if (($session_sort = $this->session('table', $this->id, 'sort')) !== NULL)
				{
					foreach ($session_sort as $i => $session)
					{
						if ($column_id == $session[0])
						{
							$added = TRUE;

							if ($order != -1 || isset($this->_sortings[$column_id]))
							{
								$this->session->set('table', $this->id, 'sort', $i, [$column_id, $order]);
							}
							else
							{
								$this->session->destroy('table', $this->id, 'sort', $i);
							}
						}
					}
				}

				if (!$added)
				{
					$this->session->append('table', $this->id, 'sort', [$column_id, $order]);
				}

				$this->sort_by($column_id, $order);
			}

			$search = $this->session('table', $this->id, 'search');
		}

		$count_results = $this->_pagination && !empty($this->output->module()->pagination) ? $this->output->module()->pagination->count() : count($this->_data);

		if ($this->_is_searchable() && $search && $this->_pagination && !empty($this->output->module()->pagination))
		{
			$this->_data = $this->output->module()->pagination->display_all();
		}
		else if (!empty($this->_sortings) && $this->_pagination && !empty($this->output->module()->pagination) && (!isset($search) || !$search))
		{
			$this->_data = $this->output->module()->pagination->get_data();
		}

		$this->_preprocessing();

		if ($this->_is_searchable())
		{
			if ($search)
			{
				$results = [];
				$words = explode(' ', trim($search));

				foreach ($this->_data as $data_id => $data)
				{
					$found = 0;
					$data = array_merge(['data_id' => $data_id], $data);

					foreach ($this->_columns as $value)
					{
						if (!isset($value['search']) || $value['search'] === NULL)
						{
							continue;
						}

						$value = $this->_parse($value['search'], $data);

						foreach ($words as $word)
						{
							if (in_string($word, $value, FALSE))
							{
								$found++;
							}
						}

						if ($found == count($words))
						{
							break;
						}
					}

					if ($found == count($words))
					{
						$results[] = $data;
					}
				}

				$this->session->set('table', $this->id, 'search', $search);

				$this->_data = $results;
				$this->_no_data = HB()->lang('Aucun rÃ©sultat ne correspond Ã  la recherche');
			}
			else
			{
				$this->session->destroy('table', $this->id, 'search');
			}

			$words = [];

			foreach ($this->_data as $data_id => $data)
			{
				$data = array_merge(['data_id' => $data_id], $data);

				foreach ($this->_columns as $value)
				{
					if (!isset($value['search']) || $value['search'] === NULL)
					{
						continue;
					}

					$this->_words[] = $value = $this->_parse($value['search'], $data);
					$words[] = $value;
				}
			}
		}

		$wrapper_data = [
			'id'                 => $this->id,
			'ajax_url'           => $this->url->ajax ? url($this->url->request) : '',
			'ajax_post'          => $this->url->ajax ? http_build_query(post()) : '',
			'search_enabled'     => !$this->_ajax && $this->_is_searchable(),
			'search_value'       => (string)$search,
			'search_source_json' => utf8_htmlentities(json_encode(array_values(array_unique(array_filter($words ?? []))))),
			'content'            => ''
		];

		if (empty($this->_data))
		{
			$output = $this->render_table_content([
				'no_data'             => TRUE,
				'no_data_message'     => $this->_no_data ?: HB()->lang('Il n\'y a rien ici pour le moment'),
				'header_columns'      => [],
				'rows'                => [],
				'footer_columns'      => [],
				'show_items_per_page' => FALSE,
				'items_per_page'      => [],
				'pagination_top'      => '',
				'pagination_bottom'   => '',
				'results_label'       => ''
			]);
		}
		else
		{
			if (!empty($this->_sortings))
			{
				$sortings = [];
				$has_valid_sorting = FALSE;

				foreach ($this->_sortings as $column => $order)
				{
					if (!isset($this->_columns[$column]) || !isset($this->_columns[$column]['sort']) || $order[0] == -1)
					{
						continue;
					}

					$tmp = [];
					foreach ($this->_data as $data_id => $data)
					{
						$data = array_merge(['data_id' => $data_id], $data);
						$tmp[] = $this->_parse($this->_columns[$column]['sort'], $data);
					}

					$sortings[] = array_map('strtolower', $tmp);
					$sortings = array_merge($sortings, $order);
					$has_valid_sorting = TRUE;
				}

				if ($has_valid_sorting)
				{
					$data = [];

					foreach ($this->_data as $key => $value)
					{
						$data[$key.' '] = $value;
					}

					$sortings[] = &$data;

					call_user_func_array('array_multisort', $sortings);

					$this->_data = [];

					foreach ($data as $key => $value)
					{
						$this->_data[trim($key)] = $value;
					}

					if ($this->_pagination && !empty($this->output->module()->pagination) && ($items_per_page = $this->output->module()->pagination->get_items_per_page()) > 0)
					{
						$this->_data = array_slice($this->_data, ($this->output->module()->pagination->get_page() - 1) * $items_per_page, $items_per_page);
					}
				}
				else
				{
					$this->_sortings = [];
					$this->session->destroy('table', $this->id, 'sort');
				}
			}

			$items_per_page = [];

			if ($this->_pagination && !empty($this->output->module()->pagination) && $this->output->module()->pagination->count() > 10)
			{
				$current_items_per_page = $this->output->module()->pagination->get_items_per_page();

				foreach ([10, 25, 50, 100] as $value)
				{
					$items_per_page[] = [
						'value'    => (string)$value,
						'selected' => $current_items_per_page == $value,
						'url'      => 'page/1/'.$value,
						'label'    => HB()->lang('%d rÃ©sultat|%d rÃ©sultats', $value, $value)
					];
				}

				$items_per_page[] = [
					'value'    => 'all',
					'selected' => $current_items_per_page == 0,
					'url'      => 'all',
					'label'    => HB()->lang('Tout afficher')
				];
			}

			$pagination_top = '';

			if ($this->_pagination && !empty($this->output->module()->pagination) && ($pagination = $this->output->module()->pagination->get_pagination()))
			{
				$pagination_top = $pagination;
			}

			$count = count($this->_data);
			$header_columns = $this->build_header_columns();
			$rows = $this->build_rows();
			$footer_columns = [];

			if ($this->_pagination && !empty($this->output->module()->pagination) && $this->output->module()->pagination->get_items_per_page() >= 50 && $count >= 50)
			{
				$footer_columns = $header_columns;
			}

			$pagination_bottom = !empty($pagination) ? $pagination : '';

			$output = $this->render_table_content([
				'no_data'             => FALSE,
				'no_data_message'     => '',
				'header_columns'      => $header_columns,
				'rows'                => $rows,
				'footer_columns'      => $footer_columns,
				'show_items_per_page' => !empty($items_per_page),
				'items_per_page'      => $items_per_page,
				'pagination_top'      => $pagination_top,
				'pagination_bottom'   => $pagination_bottom,
				'results_label'       => HB()->lang('%d rÃ©sultat|%d rÃ©sultats', $count, $count).($count < $count_results ? HB()->lang(' sur %d au total', $count_results) : '')
			]);

			if (!$this->_ajax)
			{
				$wrapper_data['content'] = $output;
				$output = $this->render_table_wrapper($wrapper_data);
			}
		}

		$this->save();

		if ($this->_ajax)
		{
			return $this->output->json([
				'search'  => [],
				'content' => $output
			]);
		}

		return $output;
	}

	public function save()
	{
		static::$_table = NULL;
		return $this;
	}

	private function _is_searchable()
	{
		foreach ($this->_columns as $value)
		{
			if (array_key_exists('search', $value))
			{
				return TRUE;
			}
		}

		return FALSE;
	}

	private function _display_header()
	{
		foreach ($this->_columns as $value)
		{
			if (array_key_exists('title', $value) || array_key_exists('sort', $value))
			{
				return TRUE;
			}
		}

		return FALSE;
	}

	private function _preprocessing()
	{
		if ($this->_preprocessing)
		{
			$this->_data = array_map($this->_preprocessing, $this->_data);
		}

		return $this;
	}

	private function _parse($content, $data = [])
	{
		if (is_a($content, 'closure'))
		{
			$content = call_user_func($content, $data);
		}

		return $content;
	}

	private function build_header_columns()
	{
		if (!$this->_display_header())
		{
			return [];
		}

		$columns = [];
		$i = 0;

		foreach ($this->_columns as $th)
		{
			$width = isset($th['size']) ? $th['size'] : FALSE;
			$sort_state = '';
			$next_order = '';

			if (!empty($this->_data) && isset($th['sort']))
			{
				if (isset($this->_sortings[$i]) && $this->_sortings[$i][0] == SORT_ASC)
				{
					$sort_state = 'asc';
					$next_order = 'desc';
				}
				else if (isset($this->_sortings[$i]) && $this->_sortings[$i][0] == SORT_DESC)
				{
					$sort_state = 'desc';
					$next_order = 'none';
				}
				else
				{
					$sort_state = 'none';
					$next_order = 'asc';
				}
			}

			$columns[] = [
				'title'      => !empty($th['title']) ? $th['title'] : '',
				'width'      => !is_bool($width) ? $width : '',
				'compact'    => $width === TRUE,
				'sortable'   => !empty($this->_data) && isset($th['sort']),
				'sort_state' => $sort_state,
				'next_order' => $next_order,
				'column'     => $i + 1,
				'align'      => !empty($th['align']) && in_array($th['align'], ['left', 'center', 'right']) ? $th['align'] : ''
			];

			$i++;
		}

		return $columns;
	}

	private function build_rows()
	{
		$rows = [];

		foreach ($this->_data as $data_id => $data)
		{
			$data = array_merge(['data_id' => $data_id], $data);
			$cells = [];

			foreach ($this->_columns as $value)
			{
				if (is_array($value['content']))
				{
					$actions = [];

					foreach ($value['content'] as $val)
					{
						$action = $this->_parse($val, $data);

						if ($action !== '' && $action !== NULL)
						{
							$actions[] = $action;
						}
					}

					$cells[] = [
						'type'    => 'actions',
						'actions' => $actions,
						'compact' => TRUE,
						'align'   => 'center'
					];
				}
				else
				{
					$cells[] = [
						'type'      => 'content',
						'content'   => $this->_parse($value['content'], $data),
						'render_td' => !isset($value['td']) || $value['td'],
						'class'     => !empty($value['class']) ? $value['class'] : '',
						'compact'   => isset($value['size']) && $value['size'] === TRUE,
						'align'     => !empty($value['align']) && in_array($value['align'], ['left', 'center', 'right']) ? $value['align'] : ''
					];
				}
			}

			$rows[] = ['cells' => $cells];
		}

		return $rows;
	}

	private function render_table_wrapper(array $data)
	{
		return $this->template->render('table/wrapper', $data, $this->legacy_table_wrapper($data));
	}

	private function render_table_content(array $data)
	{
		return $this->template->render('table/content', $data, $this->legacy_table_content($data));
	}

	private function legacy_table_wrapper(array $data)
	{
		$search = '';

		if ($data['search_enabled'])
		{
			$search = '<div class="table-search"><input data-provide="typeahead" data-items="5" data-source="'.$data['search_source_json'].'" type="text"'.($data['search_value'] !== '' ? ' value="'.utf8_htmlentities($data['search_value']).'"' : '').' placeholder="'.HB()->lang('Rechercher').'" autocomplete="off" /></div>';
		}

		return '<div class="table-area" data-table-id="'.$data['id'].'"'.($data['ajax_url'] ? ' data-ajax-url="'.$data['ajax_url'].'" data-ajax-post="'.$data['ajax_post'].'"' : '').'>'.$search.'<div class="table-content">'.$data['content'].'</div></div>';
	}

	private function legacy_table_content(array $data)
	{
		if ($data['no_data'])
		{
			return '<div>'.$data['no_data_message'].'</div>';
		}

		$output = '';

		if ($data['show_items_per_page'])
		{
			$output .= '<div><select onchange="window.location=\''.url($this->output->module()->pagination->get_url()).'/\'+$(this).find(\'option:selected\').data(\'url\')" autocomplete="off">';

			foreach ($data['items_per_page'] as $option)
			{
				$output .= '<option value="'.$option['value'].'"'.($option['selected'] ? ' selected="selected"' : '').' data-url="'.$option['url'].'">'.$option['label'].'</option>';
			}

			$output .= '</select></div>';
		}

		$output .= $data['pagination_top'];
		$output .= '<table>';

		if ($data['header_columns'])
		{
			$output .= '<thead><tr>';

			foreach ($data['header_columns'] as $column)
			{
				$classes = [];

				if ($column['compact'])
				{
					$classes[] = 'compact';
				}

				if ($column['sortable'])
				{
					$classes[] = 'sort';
					$classes[] = $column['sort_state'] === 'asc' ? 'sorting_asc' : ($column['sort_state'] === 'desc' ? 'sorting_desc' : 'sorting');
				}

				if ($column['align'])
				{
					$classes[] = 'align-'.$column['align'];
				}

				$output .= '<th'.($classes ? ' class="'.implode(' ', $classes).'"' : '').($column['width'] ? ' style="width: '.$column['width'].';"' : '').($column['sortable'] ? ' data-column="'.$column['column'].'" data-order-by="'.$column['next_order'].'"' : '').'>'.$column['title'].'</th>';
			}

			$output .= '</tr></thead>';
		}

		$output .= '<tbody>';

		foreach ($data['rows'] as $row)
		{
			$output .= '<tr>';

			foreach ($row['cells'] as $cell)
			{
				if ($cell['type'] === 'actions')
				{
					$output .= '<td class="actions">'.implode(' ', $cell['actions']).'</td>';
				}
				else if ($cell['render_td'])
				{
					$classes = [];

					if ($cell['compact'])
					{
						$classes[] = 'compact';
					}

					if ($cell['class'])
					{
						$classes[] = $cell['class'];
					}

					if ($cell['align'])
					{
						$classes[] = 'align-'.$cell['align'];
					}

					$output .= '<td'.($classes ? ' class="'.implode(' ', $classes).'"' : '').'>'.$cell['content'].'</td>';
				}
				else
				{
					$output .= $cell['content'];
				}
			}

			$output .= '</tr>';
		}

		$output .= '</tbody>';

		if ($data['footer_columns'])
		{
			$output .= '<tfoot><tr>';

			foreach ($data['footer_columns'] as $column)
			{
				$classes = [];

				if ($column['compact'])
				{
					$classes[] = 'compact';
				}

				if ($column['sortable'])
				{
					$classes[] = 'sort';
					$classes[] = $column['sort_state'] === 'asc' ? 'sorting_asc' : ($column['sort_state'] === 'desc' ? 'sorting_desc' : 'sorting');
				}

				if ($column['align'])
				{
					$classes[] = 'align-'.$column['align'];
				}

				$output .= '<th'.($classes ? ' class="'.implode(' ', $classes).'"' : '').($column['width'] ? ' style="width: '.$column['width'].';"' : '').($column['sortable'] ? ' data-column="'.$column['column'].'" data-order-by="'.$column['next_order'].'"' : '').'>'.$column['title'].'</th>';
			}

			$output .= '</tr></tfoot>';
		}

		$output .= '</table>';

		if ($data['pagination_bottom'])
		{
			$output .= '<div>'.$data['pagination_bottom'].'</div>';
		}

		$output .= '<i>'.$data['results_label'].'</i>';

		return $output;
	}
}
