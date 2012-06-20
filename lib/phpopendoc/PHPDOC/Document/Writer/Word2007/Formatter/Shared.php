<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 * 
 */
namespace PHPDOC\Document\Writer\Word2007\Formatter;

use PHPDOC\Element\ElementInterface,
    PHPDOC\Element\SectionInterface
    ;

/**
 * Shared property formats
 */
class Shared
{
    private $propertyMap;
    
    public function __construct($propertyMap = null) {
        $this->propertyMap = array();
        if ($propertyMap) {
            $this->propertyMap = $propertyMap;
        }
    }
    
    /**
     * Create a new properties DOM object for the element.
     *
     * @param mixed     $element The element to extract properties from
     * @param \DOMNode  $node    The DOMNode to interface with
     * @return boolean  Return true if any properties were processed.
     */
    public function format($element, \DOMNode $node)
    {
        if (!($element instanceof ElementInterface) and
            !($element instanceof SectionInterface)) {
            throw new \InvalidArgumentException("Argument 1 passed to " . __METHOD__
                                                . '() must implement interface ElementInterface or SectionInterface. '
                                                . 'Class ' . get_class($element) . ' given instead.');
        }

        if (!$element->hasProperties()) {
            return false;
        }

        $modified = false;
        foreach ($element->getProperties() as $key => $val) {
            $name = $this->lookup($key);
            if ($name === null) {   // invalid property
                // @todo Raise an exception? Warning? Do anything? ...
                continue;
            }

            // if the value is processed then we can continue ...
            if ($this->process($name, $val, $element, $node)) {
                continue;
            }
            
            // do not add property if the value is null
            if ($val !== null) {
                $prop = $node->ownerDocument->createElement('w:' . $name);
                if ($this->translate($name, $val, $prop)) {
                    $node->appendChild($prop);
                    $modified = true;
                }
            }
        }

        //$modified = $this->finalize($element, $node, $modified);
        return $modified;
    }

    //public function finalize($element, $node, $modified = false)
    //{
    //    // ...
    //    return $modified;
    //}

    /**
     * Translate a property value and add it to the DOM node.
     *
     * @param string $name The property name.
     * @param string $val The property value.
     * @param \DOMNode $node The DOM node to update.
     */
    public function translate($name, $val, $node, $map = null)
    {
        $method = 'translate_' . $name;
        if ($map) {
            if (isset($map[$name])) {
                $method = $map[$name];
            }
        }

        if (method_exists($this, $method)) {
            return $this->$method($name, $val, $node);
        }
        // @todo Might not be a good idea to allow values to fall through like this.
        // If no specific translation is available then assume its a simple value.
        return $this->appendSimpleValue($node, $val);
    }

    /**
     * Process a property value.
     *
     * @param string   $name    The property name.
     * @param string   $val     The property value.
     * @param mixed    $element The element being processed.
     * @param \DOMNode $node    The DOM node to update.
     */
    public function process($name, $val, $element, $node)
    {
        $method = 'process_' . $name;
        if (method_exists($this, $method)) {
            return $this->$method($name, $val, $element, $node);
        }
        return false;
    }

    /**
     * Lookup a property name alias and return the true name for it.
     *
     * @param string $name The property name/alias to lookup
     * @return string The true property name or the $name unchanged if not found.
     */
    public function lookup($name)
    {
        if (isset($this->propertyMap[$name])) {
            return $this->propertyMap[$name];
        }
        return null;
    }
    
    /**
     * Assign a simple value to the node, <w:X val="..."/>
     */
    public static function appendSimpleValue($node, $val, $key='w:val')
    {
        if (is_bool($val)) {
            $val = self::getOnOff($val);
        }
        if ($val !== null and $val !== '') {
            $node->appendChild(new \DOMAttr($key, $val));
        }
        return true;
    }

    /**
     * Return 'on', 'off' or null based on the value given.
     */
    public static function getOnOff($value)
    {
        if ($value === null) {
            return null;
        }
        if (is_bool($value)) {
            return $value ? 'on' : 'off';
        }
        $value = strtolower($value);
        if (in_array($value, array('on', 'true', 'yes', '1', 1))) {
            return 'on';
        }
        if (in_array($value, array('off', 'false', 'no', '0', 0))) {
            return 'off';
        }
        return null;
    }
}
