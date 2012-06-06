<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 * 
 */
namespace PHPDOC\Element;

use PHPDOC\Property\Properties,
    PHPDOC\Property\PropertiesInterface;

/**
 * Base "Element" class for all document elements.
 *
 * Elements can optionally subclass this base class to provide some helpful
 * shortcuts that all elements should have. If you don't subclass this base
 * class you must at least implement ElementInterface.
 * 
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
abstract class Element implements ElementInterface
{
    protected $properties;
    
    public function setProperties($properties)
    {
        if (is_array($properties)) {
            $properties = new Properties($properties);
        }
        if (!($properties instanceof PropertiesInterface)) {
            $type = gettype($properties);
            if ($type == 'object') {
                $type = get_class($properties);
            }
            throw new \InvalidArgumentException("Unexpected properties type of \"$type\" given. Expected PropertiesInterface or array.");
        }
        $this->properties = $properties;
    }
    
    public function hasProperties()
    {
        return count($this->properties) > 0;
    }
}