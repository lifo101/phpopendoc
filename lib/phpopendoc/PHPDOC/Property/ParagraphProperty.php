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
 * ParagraphProperty class represents valid properties for Paragraph's
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
class ParagraphProperty extends BaseProperty
{
    public function createPropertyMap()
    {
        $this->map = array(
            'align'     => 'jc',
            'justify'   => 'jc',
            'jc'        => 'jc',
        );
    }
    
    protected function translate_jc($value)
    {
        if ($value == 'justify') {
            $value = 'both';
        }
        return $value;
    }
}