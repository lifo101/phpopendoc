<?php

namespace PHPDOC\Element;

/**
 * The Section class is a wrapper for a single section within a document.
 *
 * A section is usually the same as a document "Page" (but not strictly).
 * A section contains 1 or more elements that make up the content for the
 * document. For example: Paragraphs of texts, images, tables, etc...
 *
 * @example
 * <code>
	$doc = new Document();
	$sec = $doc->addSection();
 * </code>
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
class Section extends BaseElement implements SectionInterface
{
	protected $name;
	
	public function __construct($name = null)
	{
		$this->name = $name;
	}
	
	public function getXML()
	{
		return '';
	}
	
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	public function getName()
	{
		return $this->name;
	}
}
