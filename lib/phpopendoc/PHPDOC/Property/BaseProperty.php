<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 * 
 */
namespace PHPDOC\Property;

use PHPDOC\Component\PropertyBag,
    PHPDOC\Property\Translator
    ;

/**
 * BaseProperty class is the super class for all element properties.
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
class BaseProperty extends PropertyBag implements PropertyInterface
{
    //protected $properties;
    protected $defaults;
    protected $map;
    protected $validProperties;
    
    public function __construct($properties = null)
    {
        $this->properties = array();
        $this->defaults = array();
        $this->map = array();
        $this->validProperties = array();

        $this->createPropertyMap();
        $this->setValidProperties();

        if ($properties) {
            if ($properties instanceof PropertyInterface) {
                $this->properties = $properties->all();
            } elseif (is_array($properties)) {
                $this->properties = $properties;
            } else {
                throw new \InvalidArgumentException("Unexpected type \"" .
                    get_class($properties) .
                    "\" given. Expected array or PropertyInterface");
            }
        }
    }

    public function set($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                parent::set($k, $v);
            }
        } else {
            return parent::set($key, $value);
        }
    }
    
    public function setValidProperties()
    {
        $this->validProperties = array_flip(array_unique(array_values($this->map)));
    }
    
    public function createPropertyMap()
    {
        // NOP
    }
    
    public function map($name)
    {
        if (array_key_exists($name, $this->map)) {
            return $this->map[$name];
        }
        return $name;
    }
    
    public function get($name, $raw = false)
    {
        if ($this->has($name)) {
            $value = parent::get($name);
        } else {
            foreach ($this->map as $alias => $prop) {
                if ($prop == $name and $this->has($alias)) {
                    $value = parent::get($alias);
                    break;
                }
            }
        }
        if (!isset($value)) {
            // @todo IDEA: Should an exception be thrown for invalid properties?
            throw new \OutOfBoundsException(sprintf("Invalid property used \"%s\" for class %s", $name, get_class($this)));
            return null;
        }
        if ($raw) {
            return $value;
        }
        return $this->translate($name, $value);
    }
    
    public function all($raw = false)
    {
        if ($raw) {
            return $this->properties;
        }
        
        $list = array();
        foreach ($this->properties as $name => $value) {
            $prop = $this->map($name);
            if (array_key_exists($prop, $this->validProperties)) {
                if ($value !== null) {
                    $value = $this->translate($prop, $value);
                    if ($value !== null) {
                        $list[$prop] = $value;
                    }
                }
            }
        }
        return $list;
    }

    public function translate($name, $value)
    {
        $func = 'translate_' . $this->map($name);
        if (method_exists($this, $func)) {
            $value = $this->$func($value);
        }
        return $value;
    }

    public function hasProperties()
    {
        return count($this) > 0;
    }
    
    public function getXML()
    {
        if ($this->hasProperties()) {
            $xml = '';
            foreach ($this as $key => $val) {
                $xml .= sprintf("            <w:%s w:val=\"%s\"/>\n", $key, $val);
            }
            return $xml;
        } else {
            return null;
        }
    }
    
    public function __toString()
    {
        return $this->getXML();
    }

    /**
     * Return an iterator for the properties
     * 
     * @internal Implements \IteratorAggregate
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->all());
    }
}