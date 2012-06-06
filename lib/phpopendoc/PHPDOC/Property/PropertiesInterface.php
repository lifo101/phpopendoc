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
 * PropertiesInterface defines the interface for element properties.
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss <lifo101@gmail.com>
 */
interface PropertiesInterface
{
    /**
     * Set a single property or a list of properties all at once.
     *
     * @param string|array $name The name of the property, or an array.
     * @param mixed        $value if $name is a string then this is the value
     *                     for that property, otherwise this should be null.
     */
    public function set($name, $value = null);
    
    /**
     * Get the value of the named property.
     *
     * @param string $name The name of the property
     * @return mixed Returns null if the property name does not exist.
     */
    public function get($name);
    
    /**
     * Return an array of all properties.
     */
    public function all();

    /**
     * Returns true if the proper name exists.
     * 
     * @param string $name The name of the property to check.
     */
    public function has($name);
    
    /**
     * Returns true if any properties are present.
     */
    public function hasProperties();
}