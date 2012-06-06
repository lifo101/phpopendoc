<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 * 
 */
namespace PHPDOC\Property;

use PHPDOC\Component\PropertyBag;

/**
 * Properties class is the super class for all element properties.
 *
 * This class provides shortcuts and flexibility for the user to make it easier
 * to maintain a list of properties with a minimal learning curve.
 * 
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
class Properties extends PropertyBag implements PropertiesInterface
{
    
    public function __construct($properties = null)
    {
        if ($properties instanceof Properties) {
            parent::__construct($properties->all());
        } else {
            parent::__construct($properties);
        }
    }

    public function set($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                parent::set($k, $v);
            }
            return $this;
        } else {
            return parent::set($key, $value);
        }
    }
    
    public function hasProperties()
    {
        return count($this) > 0;
    }
    
    //public function getXML()
    //{
    //    $dom = new \DOMDocument('1.0', 'utf-8');
    //    foreach ($this as $key => $val) {
    //        $node = $dom->createElement('w:'.$key);
    //        //$node->appendChild(new \DOMAttr('w:'.$key, $val));
    //        $attr = $dom->createAttribute('w:val');
    //        $attr->value = htmlentities($val, ENT_NOQUOTES, 'utf-8');
    //        $node->appendChild($attr);
    //        $dom->appendChild($node);
    //    }
    //    return $dom;
    //}

}