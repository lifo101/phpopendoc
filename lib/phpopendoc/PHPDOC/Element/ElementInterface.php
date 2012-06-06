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
 * ElementInterface defines the interface for document elements.
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss <lifo101@gmail.com>
 */
interface ElementInterface
{
    
    /**
     * Returns all content within the element.
     *
     * @return array An array of child content elements.
     */
    //public function getContent();
    
    /**
     * Sets the properties for the element.
     *
     * @param mixed $properties Can be an array or an instance of PropertyInterface
     */
    public function setProperties($properties);
    
    /**
     * Returns true if the element has any properties
     */
    public function hasProperties();
}