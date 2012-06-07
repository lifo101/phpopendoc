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
    
    public function __construct($elements = null, $properties = null)
    {
        parent::__construct($properties);
        if ($elements and !is_array($elements)) {
            $elements = array( $elements );
        }
        if ($elements) {
            for ($i=0, $j=count($elements); $i < $j; $i++) {
                $arg = $elements[$i];
                if ($arg instanceof ElementInterface) {
                    if ($arg instanceof TextRunInterface) {
                        $this->elements[] = $arg;
                    } else {
                        // basic Text objects are converted to TextRun's
                        $this->elements[] = new TextRun($arg);
                    }
                } elseif (is_string($arg)) {
                    // Plain strings are converted to TextRun's
                    $this->elements[] = new TextRun($arg);
                } else {
                    $type = gettype($arg);
                    if ($type == 'object') {
                        $type = get_class($arg);
                    }
                    throw new \UnexpectedValueException("Element type not an instance of \"ElementInterface\". Got \"$type\" instead.");
                }
            }
        }
    }
    
}
