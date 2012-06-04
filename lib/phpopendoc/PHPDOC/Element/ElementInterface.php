<?php

namespace PHPDOC\Element;

/**
 * ElementInterface defines the interface for document section elements.
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss <lifo101@gmail.com>
 */
interface ElementInterface
{
	/**
	 * Returns the XML representation for the element.
	 */
	public function getXML();
	public function __toString();	// shortcut for getXML()
}