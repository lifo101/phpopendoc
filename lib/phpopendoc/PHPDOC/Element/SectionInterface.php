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

interface SectionInterface extends \IteratorAggregate, \ArrayAccess, \Countable, ElementInterface
{

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
     * Return true if headers are defined
     */
    public function hasHeaders();

    /**
     * Return true if footers are defined
     */
    public function hasFooters();

    /**
     * Return defined footers
     */
    public function getFooters();
}
