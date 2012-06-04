<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 * 
 */
namespace PHPDOC\Element;

use PHPDOC\Property\PropertyInterface,
    PHPDOC\Property\TextRunProperty;

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
class TextRun extends BaseElement implements TextRunElementInterface
{
    protected $indent;
    protected $content;
    protected $properties;
    
    public function __construct($content = null, $properties = null)
    {
        $this->indent = '    ';
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
        }
    }
    
    public function getXML()
    {
        if ($this->hasContent()) {
            $xml = $this->indent . "<w:r>\n";
            if ($this->hasProperties()) {
                $xml .= $this->indent . $this->indent . "<w:rPr>\n" .
                    $this->properties .
                    $this->indent . $this->indent . "</w:rPr>\n";
            }
            foreach ($this->content as $child) {
                $xml .= $this->indent . $this->indent . $child . "\n";
            }
            $xml .= $this->indent . "</w:r>\n";
            return $xml;
        } else {
            return "<w:r/>\n";
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
