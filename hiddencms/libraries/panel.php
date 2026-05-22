<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries;

use HB\HiddenCMS\Library;

class Panel extends Library
{
	protected $_heading = [];
	protected $_footer  = [];
	protected $_data    = [];
	protected $_body;
	protected $_body_tags;
	protected $_style;
	protected $_size;

	public function __invoke()
	{
		return $this;
	}

	public function __toString()
	{
		$data = $this->template_data();

		if ($theme = $this->output->theme())
		{
			$paths = [];

			if ($theme->__path('views', 'components/panel.tpl.php', $paths))
			{
				return (string)$theme->view('components/panel.tpl.php', $data);
			}
		}

		$paths = [];

		if (HB()->__path('views', 'components/panel.tpl.php', $paths))
		{
			return (string)HB()->view('components/panel.tpl.php', $data);
		}

		return $data['legacy'];
	}

	public function title($label = '', $icon = '')
	{
		if ($this->_heading)
		{
			$this->_heading[0]	->title_if($label, $label)
								->icon_if($icon, $icon);
		}
		else
		{
			$this->heading($label, $icon);
		}

		return $this;
	}

	public function heading($label = '', $icon = '', $url = '')
	{
		if (func_num_args())
		{
			if (!is_a($label, 'HB\HiddenCMS\Libraries\Html'))
			{
				$label = $this	->label()
								->title($label)
								->icon($icon);

				if ($url)
				{
					$label->url($url);
				}
			}

			$this->_heading[] = $label;
		}
		else if ($this->__caller == $this->output->module())
		{
			$this->_heading[] = $this	->label()
										->title($this->output->data->get('module', 'title'))
										->icon($this->output->data->get('module', 'icon'));
		}
		else
		{
			$this->_heading[] = $this	->label()
										->title($this->__caller->info()->title)
										->icon($this->__caller->info()->icon);
		}

		return $this;
	}

	public function create($name)
	{
		if (($model = @$this->model2($name)) && ($action = @$model->action('create')))
		{
			$this->_heading[] = $action->__button()->align('right');
		}

		return $this;
	}

	public function body($body = '', $add_body_tags = TRUE)
	{
		if (func_get_args())
		{
			$this->_body      = $body;
			$this->_body_tags = $add_body_tags;
			return $this;
		}
		else
		{
			return $this->_body;
		}
	}

	public function footer($footer = '', $align = 'center')
	{
		if (!is_a($footer, 'HB\HiddenCMS\Libraries\Html'))
		{
			$footer = $this	->button()
							->title($footer)
							->align($align);
		}

		$this->_footer[] = $footer;

		return $this;
	}

	public function color($color)
	{
		$this->_style = 'border-'.$color;
		return $this;
	}

	public function style($style)
	{
		$this->_style = $style;
		return $this;
	}

	public function data($data, $value = '')
	{
		if (func_num_args() == 2)
		{
			$this->_data[$data] = $value;
		}
		else
		{
			$this->_data = $data;
		}

		return $this;
	}

	public function size($size = '')
	{
		if (func_num_args())
		{
			$this->_size = $size;
			return $this;
		}
		else
		{
			return $this->_size;
		}
	}

	private function template_data()
	{
		$header = '';

		if ($this->_heading)
		{
			$headers = $this->_heading;

			$header = (string)$this	->button
								->static_footer($headers, 'left')
								->append_attr('class', 'card-header')
								->tag('h6');
		}

		$footer = $this->_footer ? (string)$this->button->static_footer($this->_footer)->append_attr('class', 'card-footer') : '';
		$class = trim('card '.($this->_style ?: ''));

		$attrs = [];

		foreach ($this->_data as $key => $value)
		{
			$attrs['data-'.$key] = $value;
		}

		$legacy = '<div class="'.$class.'"'.$this->render_attrs($attrs).'>'
				.$header
				.($this->_body ? ($this->_body_tags ? '<div class="card-body">'.$this->_body.'</div>' : $this->_body) : '')
				.$footer
				.'</div>';

		return [
			'class'     => $class,
			'attrs'     => $this->render_attrs($attrs),
			'header'    => $header,
			'body'      => (string)$this->_body,
			'body_wrap' => (bool)$this->_body_tags,
			'footer'    => $footer,
			'legacy'    => $legacy
		];
	}

	private function render_attrs($attrs)
	{
		$output = '';

		foreach ($attrs as $key => $value)
		{
			$output .= ' '.$key;

			if ($value !== NULL)
			{
				$output .= '="'.utf8_htmlentities($value).'"';
			}
		}

		return $output;
	}
}


