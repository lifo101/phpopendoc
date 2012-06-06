<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 * 
 */
namespace PHPDOC\Element;

use PHPDOC\Property\PropertiesInterface,
    PHPDOC\Property\Properties
    ;

/**
 * TextRun class represents 0 or more elements within a paragraph.
 *
 * A TextRun element can contain 0 or more elements that all share the same
 * formatting properties. TextRun's are inline elements.
 *
 * @example
 * <code>
    $run = new TextRun(array(
        'The quick ',
        'brown fox ',
        'jumped over the lazy dog'
    ), array(
        'italic' => true
    ));
 * </code>
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
class TextRun extends Element implements TextRunInterface
{
    protected $content;
    
    public function __construct($content = null, $properties = null)
    {
        $this->content = array();
        if (is_array($content)) {
            foreach ($content as $element) {
                if (!($element instanceof ElementInterface)) {
                    $element = new Text($element);
                }
                $this->content[] = $element;
            }
        } elseif ($content !== null) {
            if (!($content instanceof ElementInterface)) {
                // assume we were given a plain string and covert it to Text()
                $content = new Text($content);
            }
            $this->content[] = $content;
        }

        if ($properties) {
            $this->setProperties($properties);
        } else {
            $this->properties = new Properties();
        }
    }
    
    //public function getXML()
    //{
    //    //foreach ($this as $key => $val) {
    //    //    $node = $dom->createElement('w:'.$key);
    //    //    //$node->appendChild(new \DOMAttr('w:'.$key, $val));
    //    //    $attr = $dom->createAttribute('w:val');
    //    //    $attr->value = htmlentities($val, ENT_NOQUOTES, 'utf-8');
    //    //    $node->appendChild($attr);
    //    //    $dom->appendChild($node);
    //    //}
    //    //return $dom;
    //
    //    $dom = new \DOMDocument('1.0', 'utf-8');
    //    $run = $dom->createElement('w:r');
    //    
    //    // add Run properties
    //    if ($this->hasProperties()) {
    //        $prop = $dom->createElement('w:rPr');
    //        $run->appendChild($prop);
    //        $props = $this->properties->getXML();
    //        foreach ($props->childNodes as $child) {
    //            try {
    //                $node = $props->importNode($child);
    //                $dom->appendChild($node);
    //            } catch (\Exception $e) {
    //                print $e->getMessage() . "\n";
    //            }
    //        }
    //    }
    //    
    //    // add Text content
    //    //if ($this->hasContent()) {
    //    //    foreach ($this->content as $child) {
    //    //        //print $child . "\n";
    //    //        foreach($child->getXML()->childNodes as $node) {
    //    //            //$run->appendChild($node);
    //    //        }
    //    //    }
    //    //}
    //
    //    $dom->appendChild($run);
    //    print $dom->saveXML();
    //    
    //    return $dom;
    //}
    
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }
    
    public function getContent()
    {
        return $this->content;
    }
    
    public function hasContent()
    {
        return $this->content !== null;
    }
}
