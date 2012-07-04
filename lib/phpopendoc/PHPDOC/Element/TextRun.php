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

    /**
     * Construct a new TextRun instance
     *
     * @param mixed $elements   A single element or an array of elements,
     *                          ranging from simple strings to ElementInterface
     *                          objects.
     * @param mixed $properties Custom properties.
     */
    public function __construct($elements = null, $properties = null)
    {
        // assume a style ID is being passed in if $properties is a string
        if (is_string($properties)) {
            $properties = array( 'rStyle' => $properties );
        }

        parent::__construct($properties);
        if ($elements and !is_array($elements)) {
            $elements = array( $elements );
        }
        if ($elements) {
            foreach ($elements as $element) {
                $this->addElement($element);
            }
        }
    }

    public function addElement($element)
    {
        if ($element instanceof TextInterface) {
            // if the Text element has any properties we have to transfer
            // those properties over to the TextRun since Text elements
            // can not have properties of their own.
            if ($element->hasProperties()) {
                foreach ($element->getProperties() as $key => $val) {
                    $this->properties[$key] = $val;
                }
            }
        } elseif ($element instanceof LinkInterface) {
            throw new ElementException("A Link can not be nested inside a TextRun");
        } elseif (!($element instanceof ElementInterface)) {
            $element = new Text($element);
        }
        $this->elements[] = $element;
    }
}
