<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries;

class Button extends Label
{
	protected $_disabled = FALSE;
	protected $_style    = [];
	protected $_data     = [];
	protected $_component = 'button';

	static public function footer($buttons, $default = 'left')
	{
		$output = HB()->html();

		if ($buttons)
		{
			$footers = HB()->array();

			foreach ($buttons as $footer)
			{
				$align = '';

				if (method_exists($footer, 'align'))
				{
					$align = $footer->align();
				}

				$footers->append($align ?: $default, $footer);
			}

			if ($footers->count() == 1 && $footers->get($default))
			{
				$footers->each('implode');
			}
			else
			{
				$footers->each(function($buttons, $align){
					return HB()->html()->attr('class', 'float-'.$align)->content($buttons);
				});
			}

			$output->content($footers);
		}

		return $output;
	}

	public function __invoke()
	{
		call_user_func_array('HB\\HiddenCMS\\Libraries\\Label::__invoke', func_get_args());

		$this->_template[] = function(&$content, &$attrs, &$tag){
			foreach ($this->_data as $key => $value)
			{
				$attrs['data-'.$key] = $value;
			}

			$class = [];

			if (!empty($attrs['class']))
			{
				$class[] = $attrs['class'];
			}

			if ($this->_style)
			{
				$class = array_merge($class, array_filter($this->_style, 'is_string'));

				$style = implode(';', array_map(function($a){
					return implode(': ', $a);
				}, array_filter($this->_style, 'is_array')));

				if ($style)
				{
					$attrs['style'] = $style;
				}
			}

			$class = array_values(array_unique(array_filter(preg_split('/\s+/', implode(' ', $class)))));

			if ($class)
			{
				$attrs['class'] = implode(' ', $class);
			}
			else
			{
				unset($attrs['class']);
			}
		};

		return $this;
	}

	public function __toString()
	{
		$data = $this->template_data();
		$candidates = ['components/'.$this->_component];

		if ($this->_component != 'button')
		{
			$candidates[] = 'components/button';
		}

		if ($theme = $this->output->theme())
		{
			foreach ($candidates as $candidate)
			{
				$paths = [];

				if ($theme->__path('views', $candidate.'.tpl.php', $paths))
				{
					return (string)$theme->view($candidate.'.tpl.php', $data);
				}
			}
		}

		foreach ($candidates as $candidate)
		{
			$paths = [];

			if (HB()->__path('views', $candidate.'.tpl.php', $paths))
			{
				return (string)HB()->view($candidate.'.tpl.php', $data);
			}
		}

		return parent::__toString();
	}

	public function disabled()
	{
		$this->_disabled = TRUE;
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
			$this->_data = array_merge($this->_data, $data);
		}

		return $this;
	}

	public function style($style, $value = '')
	{
		if (func_num_args() == 2)
		{
			$this->_style[] = [$style, $value];
		}
		else
		{
			$this->_style = array_merge($this->_style, explode(' ', $style));
		}

		return $this;
	}

	public function component($component = 'button')
	{
		$this->_component = trim((string)$component, '/');

		if ($this->_component === '' || strpos($this->_component, '..') !== FALSE)
		{
			$this->_component = 'button';
		}

		return $this;
	}

	public function modal($title, $icon = '')
	{
		$modal = is_a($title, 'HB\HiddenCMS\Libraries\Modal') ? $title : parent::modal($title, $icon);

		return $this->url('#')
					->data([
						'toggle' => 'modal',
						'target' => '#'.$modal->id
					]);
	}

	public function modal_ajax($url)
	{
		$this->js('modal');

		return $this->url('#')
					->data([
						'modal-ajax' => url($url)
					]);
	}

	private function template_data()
	{
		$tag = $this->_tag ?: 'span';
		$attrs = $this->_attrs;
		$content = [];

		foreach ($this->_data as $key => $value)
		{
			$attrs['data-'.$key] = $value;
		}

		if ($this->_icon)
		{
			$content[] = icon($this->_icon);
		}

		if ($this->_title)
		{
			$content[] = $this->lang($this->_title);
		}

		$content = implode(' ', $content);

		if ($content === '')
		{
			$content = $this->content();
		}

		if ($this->_url !== NULL)
		{
			$attrs['href'] = url($this->_url);
			$tag = 'a';
		}

		if ($this->_tooltip)
		{
			$attrs['title'] = $this->lang($this->_tooltip);

			if (empty($attrs['data-toggle']))
			{
				$attrs['data-toggle'] = 'tooltip';
				$attrs['data-html'] = 'true';
			}
		}
		else if ($this->_popover)
		{
			if (empty($attrs['data-toggle']))
			{
				$attrs['data-toggle'] = 'popover';
				$attrs['data-html'] = 'true';
			}

			$attrs['title'] = $this->lang($this->_popover[1]);
			$attrs['data-content'] = $this->lang($this->_popover[0]);
		}

		$class = [];

		if (!empty($attrs['class']))
		{
			$class[] = $attrs['class'];
		}

		if ($this->_style)
		{
			$class = array_merge($class, array_filter($this->_style, 'is_string'));

			$style = implode(';', array_map(function($a){
				return implode(': ', $a);
			}, array_filter($this->_style, 'is_array')));

			if ($style)
			{
				$attrs['style'] = $style;
			}
		}

		$class = array_values(array_unique(array_filter(preg_split('/\s+/', implode(' ', $class)))));

		if (!empty($class))
		{
			$attrs['class'] = implode(' ', $class);
		}
		else
		{
			unset($attrs['class']);
		}

		$attrs_without_class = $attrs;
		unset($attrs_without_class['class']);

		return [
			'tag' => $tag,
			'attrs' => $this->render_attrs($attrs),
			'attrs_except_class' => $this->render_attrs($attrs_without_class),
			'class' => !empty($attrs['class']) ? $attrs['class'] : '',
			'component' => $this->_component,
			'color' => $this->_color ?: '',
			'disabled' => $this->_disabled,
			'content' => $content
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


