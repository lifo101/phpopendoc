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
class Text extends BaseElement implements TextElementInterface
{
    protected $content;
    
    public function __construct($content = null)
    {
        $this->content = $content;
    }
    
    public function getXML()
    {
        if ($this->hasContent()) {
            // @todo xml:space needs to be a property
            return '<w:t xml:space="preserve">' . htmlentities($this->content, ENT_NOQUOTES, "UTF-8") . '</w:t>';
        } else {
            return '<w:t/>';
        }
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
}
