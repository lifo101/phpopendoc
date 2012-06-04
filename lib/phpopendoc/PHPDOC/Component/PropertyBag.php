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
 * PropertyBag implements a common container for element properties
 */
class PropertyBag implements \IteratorAggregate, \Countable, \ArrayAccess
{
    protected $properties;
    
    public function __construct($properties = null)
    {
        if (is_array($properties)) {
            $this->properties = $properties;
        } elseif ($properties instanceof PropertyBag) {
            $this->properties = $properties->all();
        } else {
            $this->properties = array();
        }
    }
    
    /**
     * Set a property by key
     *
     * @param string $key The property key name.
     * @param mixed $value The property value
     */
    public function set($key, $value)
    {
        // @todo FIXME; Allow nested keys like "foo.bar" to be set properly.
        if (strpos($key, '.') !== false) {
            throw new \InvalidArgumentException("Can not use nested key ($key) in set().");
        }
        $this->properties[$key] = $value;
        return $this;
    }
    
    /**
     * Remove a property by key
     */	
    public function remove($key)
    {
        if (strpos($key, '.') === false) {
            unset($this->properties[$key]);
            return $this;
        }

        // @todo FIXME; Allow nested keys like "foo.bar" to be removed properly.
        throw new \InvalidArgumentException('Removing nested properties is not supported at this time.');

        // dealing with nested array references is tricky... 		
        //$path = explode('.', $key);
        //if (!$path or !array_key_exists($path[0], $this->properties)) {
        //	return $this;
        //}
        //
        //$unset = false;
        //$prev =& $this->properties;
        //$root =& $this->properties[$path[0]];
        //for ($i = 1, $j = count($path); $i < $j; $i++) {
        //	$unset = true;
        //	if (is_array($root)) {
        //		if (array_key_exists($path[$i], $root)) {
        //			$prev =& $root;
        //			$root =& $root[$path[$i]];
        //		}
        //	} else {
        //		if ($i+1 == $j) {
        //			$unset = true;
        //		}
        //	}
        //}
        //if ($unset) {
        //	unset($root);
        //}
        //return $this;
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
