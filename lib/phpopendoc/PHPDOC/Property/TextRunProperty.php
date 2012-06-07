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
class TextRunProperty extends Properties
{
    public function createPropertyMap()
    {
        $this->map = array(
            'color'         => 'color',
            
            'size'          => 'sz',
            'sz'            => 'sz',
            
            'bold'          => 'b',
            'b'             => 'b',
            
            'italic'        => 'i',
            'i'             => 'i',
            
            'spacing'       => 'spacing',
            
            'caps'          => 'caps',
            
            'doublestrike'  => 'dstrike',
            'double-strike' => 'dstrike',
            'dstrike'       => 'dstrike',
        );
    }

    /**
     * "spacing" translation
     */
    protected function translate_spacing($value)
    {
        return Translator::pointToTwip($value);
    }

    /**
     * "size" translation
     */
    protected function translate_sz($value)
    {
        return Translator::pointToHalfPoint($value);
    }

    /**
     * "bold" translation
     */
    protected function translate_b($value)
    {
        if ($value === null) {
            return null;
        }
        if (is_bool($value)) {
            return $value ? 'on' : 'off';
        }
        $value = strtolower($value);
        if (in_array($value, array('on', 'true', 'yes', '1', 1))) {
            return 'on';
        }
        if (in_array($value, array('off', 'false', 'no', '0', 0))) {
            return 'off';
        }
        return null;
    }
}
