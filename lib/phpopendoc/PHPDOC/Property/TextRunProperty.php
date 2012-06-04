<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 * 
 */
namespace PHPDOC\Property;

use PHPDOC\Component\PropertyBag;

/**
 * TextRunProperty class represents valid properties for TextRun's
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
class TextRunProperty extends BaseProperty
{
    public function createPropertyMap()
    {
        $this->map = array(
            'color'     => 'color',
            
            'size'      => 'sz',
            'sz'        => 'sz',
            
            'bold'      => 'b',
            'b'         => 'b',
            
            'italic'    => 'i',
            'em'        => 'i',
            'i'         => 'i',
        );
    }
}