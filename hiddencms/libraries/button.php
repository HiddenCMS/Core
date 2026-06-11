<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries;

class Button extends Label
{
	protected $_compact  = FALSE;
	protected $_outline  = FALSE;
	protected $_disabled = FALSE;
	protected $_style    = [];
	protected $_data     = [];

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
					return HB()->html()->attr('class', (($theme = HB()->output->theme()) && $theme->info()->name == 'admin' ? $align.' floated' : 'float-'.$align))->content($buttons);
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

			if ($this->_style)
			{
				if ($classes = implode(' ', array_filter($this->_style, 'is_string')))
				{
					$attrs['class'] = trim((isset($attrs['class']) ? $attrs['class'].' ' : '').$classes);
				}

				$style = implode(';', array_map(function($a){
					return implode(': ', $a);
				}, array_filter($this->_style, 'is_array')));

				if ($style)
				{
					$attrs['style'] = $style;
				}
			}

			if ($this->_disabled)
			{
				$attrs['disabled'] = NULL;
				$attrs['aria-disabled'] = 'true';
			}
		};

		return $this;
	}

	public function __toString()
	{
		$tag     = $this->_tag;
		$attrs   = $this->_attrs;
		$content = $this->content();

		if ($this->_template)
		{
			foreach ($this->_template as $template)
			{
				call_user_func_array($template, [&$content, &$attrs, &$tag]);
			}
		}

		$content = $this->render_button_template($tag, $attrs, $content);

		if ($this->_container)
		{
			$content = call_user_func_array($this->_container, [$content])->__toString();
		}

		return $content;
	}

	private function render_button_template($tag, array $attrs, $content)
	{
		$class = isset($attrs['class']) ? $attrs['class'] : '';
		unset($attrs['class']);

		$attrs_output = '';

		foreach ($attrs as $key => $value)
		{
			$attrs_output .= ' '.$key.($value !== NULL ? '="'.utf8_htmlentities($value).'"' : '');
		}

		$data = [
			'tag'                => $tag,
			'attrs'              => $attrs,
			'attrs_output'       => $attrs_output,
			'attrs_except_class' => $attrs_output,
			'class'              => $class,
			'content'            => $content,
			'color'              => $this->_color,
			'compact'            => $this->_compact,
			'outline'            => $this->_outline,
			'disabled'           => $this->_disabled
		];

		$paths = [];
		$templates = [];

		if ($theme = $this->output->theme())
		{
			$templates[] = $theme;
		}

		$templates[] = HB();

		foreach ($templates as $template_owner)
		{
			if ($path = $template_owner->__path('views', 'components/button.tpl.php', $paths))
			{
				extract($data);
				ob_start();
				include $path;
				return ob_get_clean();
			}
		}

		return '<'.$tag.$attrs_output.($class ? ' class="'.utf8_htmlentities($class).'"' : '').'>'.$content.'</'.$tag.'>';
	}

	public function compact($compact = TRUE)
	{
		$this->_compact = $compact;
		return $this;
	}

	public function outline($outline = TRUE)
	{
		$this->_outline = $outline;
		return $this;
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
}


