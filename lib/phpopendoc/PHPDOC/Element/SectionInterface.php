<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 * 
 */
namespace PHPDOC\Element;

// @codeCoverageIgnoreStart 

interface SectionInterface extends \IteratorAggregate, \ArrayAccess, \Countable
{

    /**
     * Returns all child elements
     * 
     * The consumer will take this array of elements and generate the required
     * output to produce the section within the document.
     */
    public function getElements();

    /**
     * Returns the internal name of the Section.
     */
    public function getName();

    /**
     * Sets the internal name of the Section.
     *
     * @param string $name The name of the section
     */
    public function setName($name);

    /**
     * Sets the properties for the element.
     *
     * @param mixed $properties Can be an array or an instance of PropertyInterface
     */
    public function setProperties($properties);
    
    /**
     * Returns all properties for the element.
     */
    public function getProperties();
    
    /**
     * Returns true if the element has any properties
     */
    public function hasProperties();
}