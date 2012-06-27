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
class Element implements ElementInterface
{
    protected $properties;
    protected $elements;

    public function __construct($properties = null)
    {
        $this->elements = array();
        if ($properties) {
            $this->setProperties($properties);
        } else {
            $this->properties = new Properties();
        }
    }

    public function getInterface()
    {
        $r = new \ReflectionClass($this);
        foreach ($r->getInterfaceNames() as $interface) {
            // simply return the first interface that is not ElementInterface
            if (strpos($interface, 'ElementInterface') == false) {
                return $interface;
            }
        }
        return __NAMESPACE__ . '\\ElementInterface';  // failsafe; shouldn't happen; except in testing
    }

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
            throw new \UnexpectedValueException("Unexpected properties type of \"$type\" given. Expected PropertiesInterface or array.");
        }
        $this->properties = $properties;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function hasProperties()
    {
        return count($this->properties) > 0;
    }

    public function addElement($element)
    {
        if (!($element instanceof ElementInterface)) {
            throw new ElementException("Argument 1 passed to " . __METHOD__ . " must implement ElementInterface");
        }
        $this->elements[] = $element;
    }

    public function getElements()
    {
        return $this->elements;
    }

    public function hasElements()
    {
        return $this->elements and count($this->elements) > 0;
    }
}
