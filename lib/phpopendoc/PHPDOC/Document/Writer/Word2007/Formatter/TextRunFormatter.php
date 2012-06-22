<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 * 
 */
namespace PHPDOC\Document\Writer\Word2007\Formatter;

use PHPDOC\Element\ElementInterface,
    PHPDOC\Document\Writer\Word2007\Translator,
    PHPDOC\Document\Writer\Exception\SaveException
    ;

/**
 * Creates properties for textruns <w:r>
 */
class TextRun extends Shared
{
    
    /**
     * Property map
     */
    private static $propertyMap = array(
        'color'         => 'color',
        
        'size'          => 'sz',
        'sz'            => 'sz',
        
        'bold'          => 'b',
        'b'             => 'b',

        'emphasis'      => 'em',
        'em'            => 'em',
        
        'italic'        => 'i',
        'i'             => 'i',
        
        'underline'     => 'u',
        'u'             => 'u',
        
        'spacing'       => 'spacing',
        
        'caps'          => 'caps',
        
        'doublestrike'  => 'dstrike',
        'double-strike' => 'dstrike',
        'dstrike'       => 'dstrike',
    );
    
    public function __construct()
    {
        parent::__construct(self::$propertyMap);
    }

    /**
     * "spacing" translation
     */
    protected function translate_spacing($name, $val, $node)
    {
        return $this->appendSimpleValue($node, Translator::pointToTwip($val));
    }

    /**
     * "size" translation
     */
    protected function translate_sz($name, $val, $node)
    {
        return $this->appendSimpleValue($node, Translator::pointToHalfPoint($val));
    }

    /**
     * "bold" translation
     */
    protected function translate_b($name, $val, $node)
    {
        return $this->appendSimpleValue($node, self::getOnOff($val));
    }

    /**
     * "underline" translation
     */
    protected function translate_u($name, $val, $node)
    {
        static $valid = array(
            'single', 'words', 'double', 'thick', 'dotted', 'dottedHeavy',
            'dash', 'dashedHeavy', 'dashLong', 'dashLongHeavy', 'dotDash',
            'dashDotHeavy', 'dotDotDash', 'dashDotDotHeavy', 'wave',
            'wavyHeavy', 'wavyDouble', 'none',            
        );

        if ($val === true) {
            $val = 'single';
        }
        if (!in_array($val, $valid)) {
            throw new SaveException("Invalid underline value \"$val\". Must be one of: " . implode(',',$valid));
        }
        
        // @todo Underline actually has other attributes
        //       (color, themeColor, themeShade, themeTint)
        return $this->appendSimpleValue($node, $val);
    }

    /**
     * "emphasis" translation
     */
    protected function translate_em($name, $val, $node)
    {
        static $valid = array(
            'none', 'dot', 'comma', 'circle', 'underDot'
        );

        if ($val === true) {
            $val = 'single';
        }
        if (!in_array($val, $valid)) {
            throw new SaveException("Invalid underline value \"$val\". Must be one of: " . implode(',',$valid));
        }
        
        // @todo Underline actually has other attributes
        //       (color, themeColor, themeShade, themeTint)
        return $this->appendSimpleValue($node, $val);
    }
}
