<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HB\HiddenCMS\Libraries;

use HB\HiddenCMS\Library;

class Modal extends Library
{
	protected $_header;
	protected $_buttons = [];
	protected $_body;
	protected $_body_tags;
	protected $_size;
	protected $_template;
	protected $_callback;

	public function __invoke($title, $icon = '')
	{
		$this->id = $this->__id();

		$this->_header = is_a($title, 'HB\HiddenCMS\Libraries\Label') ? $title : $this->label($title, $icon);

		$this->output->data->append('modals', $this);

		return $this;
	}

	public function set_id($id)
	{
		$this->id = $id;
		return $this;
	}

	public function __toString()
	{
		$data = $this->template_data();
		$content = $this->render_template($data);

		if ($this->url->ajax())
		{
			return $this->output->json([
				'content' => $content
			]);
		}
		else
		{
			$this->js('modal');
			return $content;
		}
	}

	public function body($body, $add_body_tags = TRUE)
	{
		$this->_body      = $body;
		$this->_body_tags = $add_body_tags;
		return $this;
	}

	public function button($button)
	{
		$this->_buttons[] = $button;
		return $this;
	}

	public function button_prepend($button)
	{
		array_unshift($this->_buttons, $button);
		return $this;
	}

	public function dismiss($title)
	{
		array_unshift($this->_buttons, parent	::button()
												->title($title)
												->color('secondary')
												->align('right')
												->data('dismiss', 'modal'));

		return $this;
	}

	public function primary($button = '', $color = 'primary')
	{
		if (!is_a($button, 'HB\HiddenCMS\Libraries\Button'))
		{
			$button = parent::button()
							->title($button ?: $this->lang('Valider'))
							->color($color);
		}

		$this->_buttons[] = $button->align('right');

		return $this;
	}

	public function submit($button = '', $color = 'primary')
	{
		if (!is_a($button, 'HB\HiddenCMS\Libraries\Button'))
		{
			$button = parent::button_submit()
							->title($button ?: $this->lang('Valider'))
							->color($color);
		}

		$this->_buttons[] = $button->align('right');

		return $this;
	}

	public function close()
	{
		return $this->dismiss($this->lang('Fermer'));
	}

	public function cancel()
	{
		return $this->dismiss($this->lang('Annuler'));
	}

	public function large()
	{
		$this->_size = 'lg';
		return $this;
	}

	public function small()
	{
		$this->_size = 'sm';
		return $this;
	}

	public function open()
	{
		HB()->js_load('modal.open($(\'#'.$this->id.'\'));');
		return $this;
	}

	public function dispose()
	{
		$this->output->json([
			'modal' => 'dispose'
		]);
	}

	public function ajax($url)
	{
		$this	->js('modal')
				->js_load('modal.load(\''.url($url).'\');');

		return $this;
	}

	public function callback($callback)
	{
		if (!is_a($callback, 'HB\HiddenCMS\Libraries\Form2'))
		{
			$callback = $this	->form2()
								->success(function() use ($callback){
									$callback();
								});
		}

		$this->_callback = $callback;

		return $this;
	}

	public function template($template)
	{
		$this->_template = $template;
		return $this;
	}

	private function template_data()
	{
		$body = '';
		$body_content = '';

		if ($this->_body)
		{
			$body_content = $this->_body;
			$body .= $this->_body_tags ? '<div class="modal-body">'.$body_content.'</div>' : $body_content;
		}

		if ($this->_callback)
		{
			$this->_callback->check();
			$hidden = $this->form_hidden('_', $this->_callback->token());
			$body .= $hidden;
			$body_content .= $hidden;
		}

		$buttons = $this->_buttons ? (string)$this->button->static_footer($this->_buttons, 'right') : '';
		$footer = $buttons ? $this->html()->attr('class', 'modal-footer')->content($buttons) : '';
		$header = '<div class="modal-header">
						<h5 class="modal-title">'.$this->_header.'</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="'.$this->lang('Fermer').'"><span aria-hidden="true">&times;</span></button>
					</div>';

		$content = $header.$body.$footer;

		if ($this->_template)
		{
			call_user_func_array($this->_template, [&$content]);
		}

		if ($this->_callback)
		{
			$this->js('form');
		}

		return [
			'id'          => $this->id,
			'size'        => $this->_size ? ' modal-'.$this->_size : '',
			'semantic_size' => $this->semantic_size(),
			'header'      => $this->_header,
			'body'        => $body_content,
			'actions'     => $buttons,
			'content'     => $content,
			'has_form'    => (bool)$this->_callback,
			'form_action' => url($this->url->request),
			'form_method' => 'post',
			'legacy'      => $this->legacy_markup($content)
		];
	}

	private function semantic_size()
	{
		if ($this->_size == 'lg')
		{
			return 'large';
		}

		if ($this->_size == 'sm')
		{
			return 'tiny';
		}

		return '';
	}

	private function render_template($data)
	{
		if ($theme = $this->output->theme())
		{
			$paths = [];

			if ($theme->__path('views', 'components/modal.tpl.php', $paths))
			{
				return (string)$theme->view('components/modal.tpl.php', $data);
			}
		}

		$paths = [];

		if (HB()->__path('views', 'components/modal.tpl.php', $paths))
		{
			return (string)HB()->view('components/modal.tpl.php', $data);
		}

		return $data['legacy'];
	}

	private function legacy_markup($content)
	{
		$content = $this->html()
						->attr('class', 'modal-content')
						->content($content);

		if ($this->_callback)
		{
			$content = $this->html('form')
							->attr('action', url($this->url->request))
							->attr('method', 'post')
							->content($content);
		}

		return '<div id="'.$this->id.'" class="modal fade" tabindex="-1" role="dialog">
					<div class="modal-dialog'.($this->_size ? ' modal-'.$this->_size : '').'">
						'.$content.'
					</div>
				</div>';
	}
}


