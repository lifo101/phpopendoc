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
 * The Paragraph element class represents a single paragraph that can include
 * 0 or more text runs, tables, etc.
 *
 * @example
 * <code>
    $p = new Paragraph(array(
       'The quick brown fox ...',
       new TextRun(array('text', 'more text', 'even more text'))
    ));
 * </code>
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
class Paragraph extends BaseElement implements BlockElementInterface
{
    protected $indent;
    protected $content;
    protected $properties;
    
    public function __construct($content = null)
    {
        $this->indent = '';
        $this->properties = new PropertyBag();
        $this->content = array();
        if ($content and !is_array($content)) {
            $content = array( $content );
        }
        if ($content) {
            for ($i=0, $j=count($content); $i < $j; $i++) {
                $arg = $content[$i];
                if ($arg instanceof ElementInterface) {
                    // Bare Text objects are converted to a TextRun object
                    if ($arg instanceof TextRunElementInterface) {
                        $this->content[] = $arg;
                    } else {
                        // basic Text objects are converted to TextRun's
                        $this->content[] = new TextRun($arg);
                    }
                } elseif (is_string($arg)) {
                    // Plain strings are converted to TextRun's
                    $this->content[] = new TextRun($arg);
                } else {
                    throw new \InvalidArgumentException("Invalid content type of \"" . get_class($arg) . "\" given. ElementInterface expected.");
                }
            }
        }
    }
    
    public function getXML()
    {
        if ($this->hasContent()) {
            $xml = $this->indent . "<w:p>\n";
            foreach ($this->content as $child) {
                $xml .= $this->indent . $this->indent . $child . "\n";
            }
            $xml .= $this->indent . "</w:p>\n";
            return $xml;
        } else {
            return '<w:r/>';
        }
    }
    
    public function setProperties(array $properties)
    {
        foreach ($properties as $key => $val) {
            $this->properties[$key] = $val;
        }
    }
    
    public function addContent(ElementInterface $content)
    {
        $this->content[] = $content;
        return $this;
    }
    
    public function setContent(array $content = null)
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
        return count($this->content) > 0;
    }
}
