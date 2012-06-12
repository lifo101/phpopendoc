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
     * Returns true if the section has any child elements
     */
    public function hasElements();

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
     * Add a header.
     *
     * Add's a new header block to the section. Different headers can be defined
     * for different pages, eg: One header for odd, even or both pages.
     *
     * @param string $type Type of header to add 'default' (or 'both'), 'odd', 'even'
     */
    public function addHeader($type = null);

    /**
     * Add a footer.
     *
     * Add's a new footer block to the section. Different footers can be defined
     * for different pages, eg: One footer for odd, even or both pages.
     *
     * @param string $type Type of footer to add 'default' (or 'both'), 'odd', 'even'
     */
    public function addFooter($type = null);

    /**
     * Return defined headers
     */
    public function getHeaders();
    
    /**
     * Return defined footers
     */
    public function getFooters();

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
