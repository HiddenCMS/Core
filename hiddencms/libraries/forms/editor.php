<?php
/**
 * Rich-text editor backed by TinyMCE.
 */

namespace HB\HiddenCMS\Libraries\Forms;

class Editor extends Textarea
{
	public function __invoke($name)
	{
		parent::__invoke($name);

		$this->_check[] = function($post, &$data){
			if (isset($data[$this->_name]))
			{
				$data[$this->_name] = $this->sanitize(utf8_html_entity_decode($data[$this->_name], ENT_QUOTES));
				$this->_value = $data[$this->_name];
			}
		};

		$this->_template[] = function(&$input){
			$this	->css('file_picker')
					->js('file_picker')
					->js('tinymce/tinymce.min')
					->js('form_tinymce');

			$input->append_attr('class', 'wysiwyg');
		};

		return $this;
	}

	private function sanitize($html)
	{
		$html = trim((string)$html);

		if ($html === '')
		{
			return '';
		}

		$allowed = array_flip([
			'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's',
			'ul', 'ol', 'li', 'a', 'blockquote', 'code', 'pre',
			'h2', 'h3', 'h4', 'h5', 'h6',
			'table', 'thead', 'tbody', 'tr', 'th', 'td'
		]);
		$admin = (bool)$this->url->admin;
		if ($admin)
		{
			$allowed += array_flip(['div', 'span', 'h1', 'hr', 'img', 'figure', 'figcaption', 'sup', 'sub', 'caption', 'tfoot', 'colgroup', 'col', 'details', 'summary', 'video', 'audio', 'source']);
		}
		$document = new \DOMDocument('1.0', 'UTF-8');
		$previous = libxml_use_internal_errors(TRUE);

		try
		{
			$document->loadHTML('<?xml encoding="UTF-8"><html><body>'.$html.'</body></html>', LIBXML_NONET);
			$body = $document->getElementsByTagName('body')->item(0);

			$clean = function($node) use (&$clean, $allowed, $admin){
				foreach (iterator_to_array($node->childNodes) as $child)
				{
					if ($child->nodeType !== XML_ELEMENT_NODE)
					{
						continue;
					}

					$tag = strtolower($child->nodeName);

					if ($tag === 'iframe' && $admin && $child->hasAttribute('data-hb-pdf'))
					{
						$id = (int)$child->getAttribute('data-hb-pdf');
						$file = HB()->model2('file', $id);
						if ($id && $file && $file() && strtolower(extension($file->path)) === 'pdf' && $this->access('files', 'read_file', $id))
						{
							foreach (iterator_to_array($child->attributes) as $attribute){ $child->removeAttribute($attribute->name); }
							while ($child->firstChild){ $child->removeChild($child->firstChild); }
							$child->setAttribute('data-hb-pdf', $id);
							$child->setAttribute('src', $file->path());
							$child->setAttribute('title', 'Lecteur PDF');
							$child->setAttribute('width', '100%');
							$child->setAttribute('height', '640');
							$child->setAttribute('style', 'border:0;max-width:100%');
							$child->setAttribute('loading', 'lazy');
							continue;
						}
					}

					if (!isset($allowed[$tag]))
					{
						if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed'], TRUE))
						{
							$node->removeChild($child);
							continue;
						}

						$clean($child);
						while ($child->firstChild)
						{
							$node->insertBefore($child->firstChild, $child);
						}
						$node->removeChild($child);
						continue;
					}

					foreach (iterator_to_array($child->attributes) as $attribute)
					{
						$name = strtolower($attribute->name);
						$attributes = $tag === 'a' ? ['href', 'title', 'target'] : [];
						if ($admin)
						{
							$attributes = array_merge($attributes, ['style', 'title', 'id', 'dir']);
							if (in_array($tag, ['img', 'video', 'audio', 'source'], TRUE)){ $attributes = array_merge($attributes, ['src', 'alt', 'width', 'height', 'controls', 'type']); }
							if (in_array($tag, ['td', 'th', 'col'], TRUE)){ $attributes = array_merge($attributes, ['colspan', 'rowspan', 'scope', 'span']); }
							if ($name === 'style')
							{
								$styles = [];
								foreach (explode(';', $attribute->value) as $declaration)
								{
									$parts = explode(':', $declaration, 2);
									if (count($parts) === 2 && in_array(trim(strtolower($parts[0])), ['color', 'background-color', 'font-family', 'font-size', 'font-weight', 'font-style', 'text-align', 'text-decoration', 'line-height', 'vertical-align', 'width', 'height', 'max-width', 'margin-left', 'margin-right', 'padding', 'border', 'border-width', 'border-style', 'border-color', 'border-collapse', 'list-style-type'], TRUE) && preg_match('/^[a-z0-9#(),.%\s\-"\']+$/i', $parts[1]) && !preg_match('/url|expression|behavior/i', $parts[1]))
									{
										$styles[] = trim($parts[0]).':'.trim($parts[1]);
									}
								}
								$child->setAttribute('style', implode(';', $styles));
							}
							if ($name === 'src')
							{
								$src = parse_url($attribute->value);
								$origin = parse_url(url('upload/files/'));
								if (!$src || !empty($src['user']) || (isset($src['scheme']) && !in_array(strtolower($src['scheme']), ['http', 'https'], TRUE)) || (isset($src['host']) && strcasecmp($src['host'], isset($origin['host']) ? $origin['host'] : '') !== 0) || empty($src['path']) || (strpos($src['path'], '/upload/files/') === FALSE && strpos($src['path'], '/files/') === FALSE) || strpos($src['path'], '..') !== FALSE)
								{
									$child->removeAttribute($attribute->name);
								}
							}
						}
						if (!in_array($name, $attributes, TRUE))
						{
							$child->removeAttribute($attribute->name);
						}
					}

					if ($tag === 'a' && $child->hasAttribute('href'))
					{
						$href = trim($child->getAttribute('href'));
						if (!preg_match('#^(https?://|mailto:|/|\#)#i', $href))
						{
							$child->removeAttribute('href');
						}
						if ($child->getAttribute('target') === '_blank')
						{
							$child->setAttribute('rel', 'noopener noreferrer');
						}
					}

					$clean($child);
				}
			};

			$clean($body);
			$output = '';
			foreach ($body->childNodes as $child)
			{
				$output .= $document->saveHTML($child);
			}

			return trim($output);
		}
		finally
		{
			libxml_clear_errors();
			libxml_use_internal_errors($previous);
		}
	}
}
