<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 * 
 */
namespace PHPDOC;

use PHPDOC\Element\Section,
    PHPDOC\Element\SectionInterface,
    PHPDOC\Property\Properties
    ;

/**
 * The Document class is the main object interface that allows you to create a
 * document structure that can be saved using one of the Document\Writer
 * classes.
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
class Document implements \ArrayAccess, \Countable
{
    protected $sections;
    protected $currentSection;
    protected $properties;
    
    public function __construct()
    {
        $this->properties = new Properties();
        $this->sections = array();
    }
    
    //public function setProperty($key, $value)
    //{
    //    $this->properties[$key] = $value;
    //    return $this;
    //}
    //
    //public function setProperties($properties)
    //{
    //    if ($properties instanceof PropertyBag) {
    //        $this->properties = $properties;
    //    } elseif (is_array($properties)) {
    //        foreach ($properties as $key => $val) {
    //            $this->properties[$key] = $val;
    //        }
    //    } else {
    //        throw new \InvalidArgumentException("Invalid type \"" . get_class($properties) . "\" given. Expected PropertyBag or array.");
    //    }
    //    return $this;
    //}
    
    
    /**
     * Add a new section to the document
     *
     * All elements in a document are presented within sections (each section
     * is essentially a "Page"; but not strictly).
     *
     * @param mixed $name The section name or an instance of SectionInterface
     */
    public function addSection($name = null, $properties = null)
    {
        if ($name instanceof SectionInterface) {
            $section = $name;
        } else {
            $section = new Section($name, $properties);
        }
        $this->currentSection = $section->getName();
        $this->sections[$this->currentSection] = $section;
        return $section;
    }
    
    /**
     * Return true if the section exists
     * 
     * @param string $name The section name
     */
    public function hasSection($name)
    {
        return array_key_exists($name, $this->sections);
    }
    
    /**
     * Return all sections.
     */
    public function getSections()
    {
        return $this->sections;
    }
    
    /**
     * Return a section by name or the current section if no name given.
     *
     * @param string $name The name of the section. Null for current.
     */
    public function getSection($name = null)
    {
        if ($name === null) {
            if ($this->currentSection === null) {
                throw new \OutOfBoundsException('No sections defined');
            }
            $name = $this->currentSection;
        }
        
        if (!array_key_exists($name, $this->sections)) {
            throw new \OutOfBoundsException("Unknown section name \"$name\"");
        }
        
        return $this->sections[$name];
    }
    
    /**
     * Remove a section by name.
     *
     * No exception is thrown if the section name does not exist.
     *
     * @param string $name The section name
     */
    public function removeSection($name)
    {
        unset($this->sections[$name]);
    }
    
    /**
     * Returns the total sections currently defined.
     *
     * @internal Implements \Countable
     */
    public function count()
    {
        return count($this->sections);
    }
    
    /**
     * Set property by key name
     *
     * @internal Implements \ArrayAccess
     */
    public function offsetSet($ofs, $value)
    {
        if ($value instanceof SectionInterface) {
            if (!empty($ofs)) {
                $value->setName($ofs);
            }
        } else {
            $type = gettype($value);
            if ($type == 'object') {
                $type = get_class($value);
            }
            throw new \UnexpectedValueException("Assignment value not an instance of \"SectionInterface\". Got \"$type\" instead.");
        }

        return $this->addSection($value);
    }
    
    /**
     * Determine if property exists by key name
     *
     * @internal Implements \ArrayAccess
     */
    public function offsetExists($ofs)
    {
        return $this->hasSection($ofs);
    }
    
    /**
     * Remove property by key name
     *
     * @internal Implements \ArrayAccess
     */
    public function offsetUnset($ofs)
    {
        unset($this->sections[$ofs]);
    }
    
    /**
     * Get property by key name
     *
     * @internal Implements \ArrayAccess
     */
    public function offsetGet($ofs)
    {
        return $this->getSection($ofs);
    }
}
