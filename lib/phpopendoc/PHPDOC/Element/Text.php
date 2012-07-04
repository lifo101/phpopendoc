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
 * A Text element can contain a single string of text. It can not have
 * child elements.
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
class Text extends Element implements TextInterface
{
    protected $content;

    public function __construct($content = null, $properties = null)
    {
        // assume a style ID is being passed in if $properties is a string
        if (is_string($properties)) {
            $properties = array( 'rStyle' => $properties );
        }

        parent::__construct($properties);
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

    public function getElements()
    {
        return array();
    }

    public function hasElements()
    {
        return false;
    }
}
