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
 * The Field class represents a dynamic field that is rendered via Runtime
 * within a document. eg: Page numbers, date, etc...
 *
 * @example
 * <code>
 * </code>
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
class Field extends Element implements FieldInterface
{
    protected $type;
    protected $instruction;
    protected $params;

    public function __construct($instruction, $elements = null, $properties = null)
    {
        parent::__construct($properties);

        $parts = explode(' ', $instruction, 2);

        // @todo Need to handle parameters better
        $this->instruction = array_shift($parts);
        $this->params = $parts ? array_shift($parts) : null;

        $this->type = 'simple';

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
        if (!($element instanceof ElementInterface)) {
            $element = new TextRun($element);
        }
        $this->elements[] = $element;
    }

    public function getInstruction()
    {
        return $this->instruction;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getType()
    {
        return $this->type;
    }
}
