<?php

namespace PHPDOC\Element;

use PHPDOC\Component\PropertyBag;

/**
 * The Text element class represents a single piece of text within a document.
 *
 * A text element can contain a single string of text that can have styles.
 *
 * @example
 * <code>
 * </code>
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
class TextRun extends BaseElement implements TextRunElementInterface
{
	protected $indent;
	protected $content;
	protected $styles;
	
	public function __construct($content = null)
	{
		$this->indent = '    ';
		$this->content = array();
		if (is_array($content)) {
			foreach ($content as $element) {
				if (!($element instanceof ElementInterface)) {
					$element = new Text($element);
				}
				$this->content[] = $element;
			}
		} elseif ($content !== null) {
			if (!($content instanceof ElementInterface)) {
				$content = new Text($content);
			}
			$this->content[] = $content;
		}
		$this->styles = new PropertyBag();
	}
	
	public function getXML()
	{
		if ($this->hasContent()) {
			$xml = $this->indent . "<w:r>\n";
			foreach ($this->content as $child) {
				$xml .= $this->indent . $this->indent . $child . "\n";
			}
			$xml .= $this->indent . "</w:r>";
			return $xml;
		} else {
			return "<w:r/>\n";
		}
	}
	
	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}
	
	public function getContent()
	{
		return $this->content;
	}
	
	public function hasContent()
	{
		return $this->content !== null;
	}
}
