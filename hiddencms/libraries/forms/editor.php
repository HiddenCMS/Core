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
			$this	->js('https://cdn.jsdelivr.net/npm/tinymce@6.8.5/tinymce.min.js')
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
		$document = new \DOMDocument('1.0', 'UTF-8');
		$previous = libxml_use_internal_errors(TRUE);

		try
		{
			$document->loadHTML('<?xml encoding="UTF-8"><html><body>'.$html.'</body></html>', LIBXML_NONET);
			$body = $document->getElementsByTagName('body')->item(0);

			$clean = function($node) use (&$clean, $allowed){
				foreach (iterator_to_array($node->childNodes) as $child)
				{
					if ($child->nodeType !== XML_ELEMENT_NODE)
					{
						continue;
					}

					$tag = strtolower($child->nodeName);

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
						if ($tag !== 'a' || !in_array(strtolower($attribute->name), ['href', 'title', 'target'], TRUE))
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
