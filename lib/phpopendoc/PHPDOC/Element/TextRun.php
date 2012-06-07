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
    $run = new TextRun('The quick brown fox jumped over the lazy dog.');
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
    
    public function __construct($elements = null, $properties = null)
    {
        parent::__construct($properties);
        if (is_array($elements)) {
            foreach ($elements as $element) {
                if (!($element instanceof ElementInterface)) {
                    $element = new Text($element);
                }
                $this->elements[] = $element;
            }
        } elseif ($elements !== null) {
            if (!($elements instanceof ElementInterface)) {
                // assume we were given a plain string and covert it to Text()
                $elements = new Text($elements);
            }
            $this->elements[] = $elements;
        }
    }
    
}
