<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 * 
 */
namespace PHPDOC\Element;

use PHPDOC\Property\Properties;

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
class Paragraph extends Element
{
    protected $content;
    
    public function __construct($content = null, $properties = null)
    {
        $this->content = array();
        if ($content and !is_array($content)) {
            $content = array( $content );
        }
        if ($content) {
            for ($i=0, $j=count($content); $i < $j; $i++) {
                $arg = $content[$i];
                if ($arg instanceof ElementInterface) {
                    if ($arg instanceof TextRunInterface) {
                        $this->content[] = $arg;
                    } else {
                        // basic Text objects are converted to TextRun's
                        $this->content[] = new TextRun($arg);
                    }
                } elseif (is_string($arg)) {
                    // Plain strings are converted to TextRun's
                    $this->content[] = new TextRun($arg);
                } else {
                    $type = gettype($value);
                    if ($type == 'object') {
                        $type = get_class($value);
                    }
                    throw new \UnexpectedValueException("Content type not an instance of \"ElementInterface\". Got \"$type\" instead.");
                }
            }
        }

        if ($properties) {
            $this->setProperties($properties);
        } else {
            $this->properties = new Properties();
        }
    }
    
    public function addContent(ElementInterface $content)
    {
        $this->content[] = $content;
        return $this;
    }
    
    public function setContent($content = null)
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

    //public function getXML()
    //{
    //    if ($this->hasContent()) {
    //        $xml = $this->indent . "<w:p>\n";
    //
    //        // output properties
    //        if ($this->hasProperties()) {
    //            $xml .= $this->indent . $this->indent . "<w:pPr>\n" .
    //                $this->properties .
    //                $this->indent . $this->indent . "</w:pPr>\n";
    //        }
    //        
    //        // output content
    //        foreach ($this->content as $child) {
    //            $xml .= $this->indent . $this->indent . $child . "\n";
    //        }
    //
    //        $xml .= $this->indent . "</w:p>\n";
    //        return $xml;
    //    } else {
    //        return '<w:r/>';
    //    }
    //}
}
