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
 * The TableCell element class represents a single cell within a table row and
 * is usually not used directly by users.
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
class TableCell extends TableRow
{

    public function addElement($element)
    {
        if ($element instanceof ElementInterface) {
            $this->elements[] = $element;
        } elseif (is_string($element)) {
            // Plain strings are converted
            $this->elements[] = new Text($element);
        } else {
            $type = gettype($element);
            if ($type == 'object') {
                $type = get_class($element);
            }
            throw new \UnexpectedValueException("Element type not an instance of \"ElementInterface\". Got \"$type\" instead.");
        }
    }

}
