<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * This class can be used independently of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 * 
 */
namespace PHPDOC\Component;

/**
 * PropertyBag implements a common container for properties.
 *
 * PropertyBag provides language shortcuts to make it easier to set/get
 * properties on the object w/o having to always use setter/getter methods
 * directly. The developer can choose which method is best for them.
 * For example:
 *      $prop->set('foo', 'bar');
 *      $prop['foo'] = 'bar';
 *      $prop->setFoo('boo');
 *
 * Sub-keys can also be used to easly create sub-arrays w/o long php syntax.
 * For example:
 *      $prop->set('foo.bar', 'baz');
 *      $prop['foo.bar'] = 'baz';
 *
 *      NOTE: It should be obvious, but you can not use __call magic
 *      ($prop->setVariableName() with nested arrays.
 */
class PropertyBag implements \IteratorAggregate, \Countable, \ArrayAccess
{
    protected $properties;
    
    public function __construct($properties = null)
    {
        if (is_array($properties)) {
            $prop = $properties;
        } elseif ($properties instanceof PropertyBag) {
            $prop = $properties->all();
        } else {
            $prop = array();
        }

        $this->properties = array();
        foreach ($prop as $key => $val) {
            if (is_array($key)) {
                foreach ($key as $k => $v) {
                    $this->set($k, $v);
                }
            } else {
                $this->set($key, $val);
            }
        }
    }
    
    /**
     * Intercept calls to ->set"Property"(...) where "Property" is the name of
     * a property to set (case-sensitive).
     */
    public function __call($name, $args)
    {
        if (substr($name, 0, 3) == 'set') {
            if (!count($args)) {
                $trace = debug_backtrace();
                throw new \InvalidArgumentException(sprintf(
                    "Missing parameter 1 in call to %s() in %s:%s",
                    $name, $trace[0]['file'], $trace[0]['line']
                ));
            }
            $key = substr($name, 3);
            return $this->set($key, $args[0]);
        }
        throw new \BadMethodCallException("Undefined method \"$name\"");
    }
    
    /**
     * Set a property by key
     *
     * @param string $key The property key name.
     * @param mixed $value The property value
     */
    public function set($key, $value)
    {
        if (strpos($key, '.') === false) {
            $this->properties[$key] = $value;
        } else {
            $ref =& $this->_getArrayRef($key, true);
            if ($ref !== null) {
                $ref = $value;
            }
        }
        return $this;
    }
    
    private function &_getArrayRef($path, $auto_create = false)
    {
        $keys = array_filter(array_map('trim', explode('.', $path)), function($s){ return !empty($s); });
        $ref =& $this->properties;

        for ($i = 0, $j = count($keys); $i < $j; $i++) {
            $key = $keys[$i];
            if (!isset($ref[$key])) {
                if ($i < $j) {
                    if ($auto_create) {
                        $ref[$key] = array();   // start new nested-array
                    } else {
                        return null;
                    }
                }
            }
            $ref =& $ref[$key];
        }
        
        return $ref;
    }

    /**
     * Remove a property by key
     */	
    public function remove($key)
    {
        if (strpos($key, '.') === false) {
            unset($this->properties[$key]);
            return $this;
        } else {
            // @todo FIXME; Allow nested keys like "foo.bar" to be removed properly.
            // I think the only way to make this work would be to iterate over
            // the entire array, copying each node a new array that is not the
            // removed node...
            throw new \InvalidArgumentException('Removing nested properties is not supported at this time.');
        }

        return $this;
    }
    
    /**
     * Return an array of all properties
     */
    public function all()
    {
        return $this->properties;
    }
    
    /**
     * Return true/false if the key exists
     */
    public function has($key)
    {
        if (strpos($key, '.') === false) {
            return array_key_exists($key, $this->properties);
        }

        $path = explode('.', $key);
        if (!$path or !array_key_exists($path[0], $this->properties)) {
            return false;
        }

        $root = $this->properties[$path[0]];
        for ($i = 1, $j = count($path); $i < $j; $i++) {
            if (is_array($root)) {
                if (array_key_exists($path[$i], $root)) {
                    $root = $root[$path[$i]];
                } else {
                    return false;
                }
            } else {
                // if we're at the last path node then we have a valid value
                return ($i+1 == $j);
            }
        }

        return true;
    }
    
    /**
     * Return a property by key or path
     *
     * @param string  $key     The property key name (or path)
     * @param mixed   $default The default value if the key doesn't exist
     * @param boolean $deep    If true, a path like foo.bar will find deeper items
     */
    public function get($key, $default = null, $deep = true)
    {
        // shortcut if we won't want a deep traversal or if no nested path is given
        if (!$deep || strpos($key, '.') === false) {
            return array_key_exists($key, $this->properties) ? $this->properties[$key] : $default;
        }

        $path = explode('.', $key);
        if (!$path or !array_key_exists($path[0], $this->properties)) {
            return $default;
        }
        
        // loop over the "dotted" path until we find the nested value
        $value = $this->properties[$path[0]];
        for ($i = 1, $j = count($path); $i < $j; $i++) {
            // nested key does not exist?
            if (!is_array($value) or !array_key_exists($path[$i], $value)) {
                return $default;
            }
            $value = $value[$path[$i]];
        }

        return $value;
    }
    
    /**
     * Return an iterator for the properties
     * 
     * @internal Implements \IteratorAggregate
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->properties);
    }
    
    /**
     * Return the number of properties
     *
     * @internal Implements \Countable
     */
    public function count()
    {
        return count($this->properties);
    }
    
    /**
     * Set property by key name
     *
     * @internal Implements \ArrayAccess
     */
    public function offsetSet($ofs, $value)
    {
        $this->set($ofs, $value);
    }
    
    /**
     * Determine if property exists by key name
     *
     * @internal Implements \ArrayAccess
     */
    public function offsetExists($ofs)
    {
        return $this->has($ofs);
    }
    
    /**
     * Remove property by key name
     *
     * @internal Implements \ArrayAccess
     */
    public function offsetUnset($ofs)
    {
        $this->remove($ofs);
    }
    
    /**
     * Get property by key name
     *
     * @internal Implements \ArrayAccess
     */
    public function offsetGet($ofs)
    {
        return $this->get($ofs);
    }
}
