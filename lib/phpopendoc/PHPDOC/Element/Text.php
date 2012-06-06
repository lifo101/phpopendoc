<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 * 
 */
namespace PHPDOC\Element;

use PHPDOC\Component\PropertyBag;

/**
 * Text element represents a single piece of text within a paragraph.
 *
 * A Text element can contain a single string of text.
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
class Text extends Element implements TextInterface
{
    protected $content;
    
    public function __construct($content = null)
    {
        $this->content = $content;
    }
    
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

    //public function getXML()
    //{
    //    $dom = new \DOMDocument('1.0', 'utf-8');
    //    $node = $dom->createElement('w:t', $this->content !== null ? $this->content : '');
    //    $dom->appendChild($node);
    //
    //    $attr = $dom->createAttribute('xml:space');
    //    $attr->value = 'preserve';
    //    $node->appendChild($attr);
    //    
    //    return $dom;
    //}
    
}
