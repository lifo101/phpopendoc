<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 * 
 */
namespace PHPDOC\Document\Writer\Word2007;

use PHPDOC\Element\ElementInterface;

/**
 * Formatter class will update a DOM node with the properties found on an
 * Element.
 *
 * Some properties have aliases to make it easier for the user to use them
 * within their code. Eg: Instead of using 'sz' (WordML property for size) to
 * specify the size of something the user can use 'size' instead. Also,
 * all "point" types will automatically be converted from points to whatever
 * measurement is required for the property, as needed. eg: 10pt to 200twips.
 *
 */
class Formatter
{
    
    private $cache;
    
    public function __construct()
    {
        $this->cache = array();
    }
    
    /**
     * Factory method to set the proper properties for the element given.
     *
     * @param mixed     $element The element to extract properties from
     * @param \DOMNode  $node    The DOMNode to append properties to
     */
    public function format($element, \DOMNode $node)
    {
        if (!($element instanceof ElementInterface)) {
            throw new \InvalidArgumentException(
                "Argument 1 passed to " . __METHOD__ . '() must implement '
                . 'interface ElementInterface. '
                . 'Class ' . get_class($element) . ' given instead.'
            );
        }
        
        $interface = $element->getInterface();

        // instantiate the formatter class, if needed
        if (!isset($this->cache[$interface])) {
            $class = str_replace('Interface', '', $interface);
            if (($pos = strrpos($class, '\\')) !== false) {    // remove namespace
                $class = substr($class, $pos+1);
            }
            $class = __CLASS__ . '\\' . $class;
            if (class_exists($class)) {
                $this->cache[$interface] = new $class();
            } else {
                $this->cache[$interface] = null;
            }
        }
        
        if ($this->cache[$interface]) {
            return $this->cache[$interface]->format($element, $node);
        }
    }
}
