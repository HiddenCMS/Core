<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Displayables;

use HB\HiddenCMS\Displayable;

class Widget extends Displayable
{
	protected $_id;
	protected $_widget;
	protected $_style;
	protected $_size;

	public function __invoke($widget = 0)
	{
		return is_int($widget) ? $this->widget_id($widget) : forward_static_call_array('HB\HiddenCMS\Addons\Widget::__load', [HB(), func_get_args()]);
	}

	public function __sleep()
	{
		return ['_widget', '_style', '_size'];
	}

	public function id($id)
	{
		$this->_id = $id;
		return $this;
	}

	public function widget_id($widget = NULL)
	{
		if (func_num_args())
		{
			$this->_widget = $widget;
			return $this;
		}
		else
		{
			return $this->_widget;
		}
	}

	public function style($style = NULL)
	{
		if (func_num_args())
		{
			$this->_style = $style;
			return $this;
		}
		else
		{
			return $this->_style;
		}
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

	public function __toString()
	{
		$widget_data = HB()->db->from('widgets')
									->where('widget_id', $this->_widget)
									->row();

		if ($widget_data && ($widget = HB()->widget($widget_data['widget'])) && $widget->is_enabled())
		{
			$widget->data = HB()->array;
			
			$output = $widget->output($widget_data['type'], HB()->storage->decode($widget_data['settings']));

			$style = function($output) use ($widget_data){
				if (is_a($output, 'HB\HiddenCMS\Libraries\Panel'))
				{
					if (!empty($widget_data['title']))
					{
						$output->title($widget_data['title']);
					}

					if (!empty($this->_style))
					{
						$output->style($this->_style);
					}
				}
			};

			if (is_array($output) || is_a($output, 'ArrayAccess'))
			{
				array_walk_recursive($output, $style);
			}
			else
			{
				$style($output);
			}

			if ($widget_data['widget'] == 'module')
			{
				$type   = 'module';
				$module = HB()->output->module();
				$name   = $module->info()->name;
			}
			else
			{
				$type = 'widget';
				$name = $widget_data['widget'];
			}

			return '<div class="'.$type.' '.$type.'-'.$name.($this->_id !== NULL ? ' live-editor-widget" data-widget-id="'.$this->_id.'" data-widget-style="'.$this->_style.'" data-title="'.$$type->info()->title.'"' : '"').'>'.$output.'</div>';
		}

		return '';
	}
}


