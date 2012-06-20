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
class Paragraph extends Element implements ParagraphInterface, BlockInterface
{
    
    public function __construct($elements = null, $properties = null)
    {
        parent::__construct($properties);
        if ($elements and !is_array($elements)) {
            $elements = array( $elements );
        }
        if ($elements) {
            foreach ($elements as $e) {
                $this->addElement($e);
            }
        }
    }
    
    public function addElement($element)
    {
        if ($element instanceof ElementInterface) {
            if (($element instanceof TextRunInterface) or
                ($element instanceof LinkInterface)) {
                $this->elements[] = $element;
            } else {
                // Any other element is automatically wrapped
                $this->elements[] = new TextRun($element);
            }
        } elseif (is_string($element)) {
            // Plain strings are converted to TextRun's
            $this->elements[] = new TextRun($element);
        } else {
            $type = gettype($element);
            if ($type == 'object') {
                $type = get_class($element);
            }
            throw new \UnexpectedValueException("Element type not an instance of \"ElementInterface\". Got \"$type\" instead.");
        }
    }
}
