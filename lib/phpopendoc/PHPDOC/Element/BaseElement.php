<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 * 
 */
namespace PHPDOC\Element;

/**
 * Base "Element" class for all elements.
 *
 * Elements can optionally subclass this base class to provide some helpful
 * shortcuts that all elements should have. If you don't subclass this base
 * class you must at least implement ElementInterface.
 * 
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
abstract class BaseElement implements ElementInterface
{
    public function __toString()
    {
        return $this->getXML();
    }
}