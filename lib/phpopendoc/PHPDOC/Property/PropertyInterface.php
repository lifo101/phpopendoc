<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 * 
 */
namespace PHPDOC\Property;

/**
 * PropertyInterface defines the interface for element properties.
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss <lifo101@gmail.com>
 */
interface PropertyInterface
{
    /**
     * Called by the constructor to setup the default properties.
     *
     * Each element has its own set of default and valid properties that can be
     * used. This should define all those properties so user specified
     * properties can be verified.
     */
    public function setValidProperties();

    /**
     * Create the property map.
     *
     * A property map allows aliases to be used for certain properties. For
     * example: instead of using the WordprocessingML property of 'sz' a user
     * could use 'size' instead.
     *
     */
    public function createPropertyMap();
    
    /**
     * Maps a property alias to the actual WordprocessingML property name.
     *
     * @param string $name Property alias name to map. If an alias does not
     *                     exist then the name is returned as-is.
     */
    public function map($name);
    
    /**
     * Performs any translation required on the property value given.
     */ 
    public function translate($name, $value);
    
    /**
     * Returns true if any properties are present.
     *
     */
    public function hasProperties();
    
    /**
     * Returns the XML representation of all properties set
     */
    public function getXML();

    
    /**
     * Set a single property or a list of properties all at once.
     *
     * @param string|array $name The name of the property, or an array.
     * @param mixed        $value if $name is a string then this is the value
     *                     for that property, otherwise this should be null.
     */
    public function set($name, $value = null);
    
    /**
     * Get the value of the named property. The property will be mapped and
     * converted as-needed unless $raw is true.
     *
     * @param string $name The name of the property
     * @param boolean $raw If true the un-converted property is returned.
     * @return mixed Returns null if the property name does not exist.
     */
    public function get($name, $raw = false);
    
    /**
     * Returns true if the proper name exists.
     * 
     * @param string $name The name of the property to check.
     */
    public function has($name);
    
    /**
     * Return an array of all properties
     *
     * @param boolean $raw If true the raw properties are returned, otherwise
     *                     the mapped and translated properties are returned.
     */
    public function all($raw = false);
}