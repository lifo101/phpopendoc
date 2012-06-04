<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 * 
 */
namespace PHPDOC\Element;

use PHPDOC\Property\PropertyInterface,
    PHPDOC\Property\TextRunProperty;

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
    protected $properties;
    
    public function __toString()
    {
        return $this->getXML();
    }
    
    public function getPropertyClass()
    {
        return str_replace('\Element', '\Property', get_class($this)) . 'Property';
    }
    
    public function setProperties($properties)
    {
        $class = $this->getPropertyClass();
        $this->properties = $properties instanceof PropertyInterface ? $properties : new $class($properties);
    }
    
    public function hasProperties()
    {
        return count($this->properties) > 0;
    }
}