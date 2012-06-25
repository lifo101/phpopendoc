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
 * The TableRow element class represents a single row within a table and is
 * usually not used directly by users.
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
class TableRow extends Element implements BlockInterface
{
    
    public function addElement($element)
    {
        if ($element instanceof ElementInterface) {
            if ($element instanceof BlockInterface) {
                $this->elements[] = $element;
            } else {
                // Any other element is automatically wrapped
                $this->elements[] = new Paragraph($element, $element->getProperties());
            }
        } elseif (is_string($element)) {
            // Plain strings are converted to Paragraph
            $this->elements[] = new Paragraph($element);
        } else {
            $type = gettype($element);
            if ($type == 'object') {
                $type = get_class($element);
            }
            throw new \UnexpectedValueException("Element type not an instance of \"ElementInterface\". Got \"$type\" instead.");
        }
    }

    public function getInterface()
    {
        return get_class($this) . 'Interface';
    }

}
