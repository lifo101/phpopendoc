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
class TextRunFormatter extends Shared
{
    /**
     * Property aliases
     */
    private static $aliases = array(
        'size'          => 'sz',
        'bold'          => 'b',
        'emphasis'      => 'em',
        'italic'        => 'i',
        'underline'     => 'u',
        'doublestrike'  => 'dstrike',
        'double-strike' => 'dstrike',
        'style'         => 'rStyle',
        'valign'        => 'vertAlign',
    );

    protected function initMap()
    {
        parent::initMap(self::$aliases);
        $this->map = array(
            'b'                     => 'bool',
            'bdr'                   => 'border',
            'caps'                  => 'caps',
            'color'                 => 'color',
            'dstrike'               => 'bool',
            'effect'                => 'text',
            'em'                    => 'bool',
            'emboss'                => 'bool',
            'fitText'               => 'fittext',
            'highlight'             => 'text',
            'i'                     => 'bool',
            'imprint'               => 'bool',
            'kern'                  => 'kern',      // hps measurement
            'noProof'               => 'bool',
            'outline'               => 'bool',
            'position'              => 'position',  // hps measurement
            'rStyle'                => 'text',
            'rtl'                   => 'bool',
            'shadow'                => 'bool',
            'shd'                   => 'shd',
            'smallCaps'             => 'bool',
            'snapToGrid'            => 'bool',
            'spacing'               => 'spacing',
            'specVanish'            => 'bool',
            'strike'                => 'strike',
            'sz'                    => 'size',      // hps measurement
            'u'                     => 'bool',
            'vanish'                => 'bool',
            'vertAlign'             => 'text',
            'w'                     => 'decimal',   // actually a percent 0-100%
            'webHidden'             => 'bool',
        ) + $this->map;
    }

    ///**
    // * "spacing" translation
    // */
    //protected function translate_spacing($name, $val, $node)
    //{
    //    return $this->appendSimpleValue($node, Translator::pointToTwip($val));
    //}
    //
    ///**
    // * "size" translation
    // */
    //protected function translate_sz($name, $val, $node)
    //{
    //    return $this->appendSimpleValue($node, Translator::pointToHalfPoint($val));
    //}
    //
    ///**
    // * "bold" translation
    // */
    //protected function translate_b($name, $val, $node)
    //{
    //    return $this->appendSimpleValue($node, self::getOnOff($val));
    //}
    //
    ///**
    // * "underline" translation
    // */
    //protected function translate_u($name, $val, $node)
    //{
    //    static $valid = array(
    //        'single', 'words', 'double', 'thick', 'dotted', 'dottedHeavy',
    //        'dash', 'dashedHeavy', 'dashLong', 'dashLongHeavy', 'dotDash',
    //        'dashDotHeavy', 'dotDotDash', 'dashDotDotHeavy', 'wave',
    //        'wavyHeavy', 'wavyDouble', 'none',
    //    );
    //
    //    if ($val === true) {
    //        $val = 'single';
    //    }
    //    if (!in_array($val, $valid)) {
    //        throw new SaveException("Invalid underline value \"$val\". Must be one of: " . implode(',',$valid));
    //    }
    //
    //    // @todo Underline actually has other attributes
    //    //       (color, themeColor, themeShade, themeTint)
    //    return $this->appendSimpleValue($node, $val);
    //}
    //
    ///**
    // * "emphasis" translation
    // */
    //protected function translate_em($name, $val, $node)
    //{
    //    static $valid = array(
    //        'none', 'dot', 'comma', 'circle', 'underDot'
    //    );
    //
    //    if ($val === true) {
    //        $val = 'dot';
    //    }
    //    if (!in_array($val, $valid)) {
    //        throw new SaveException("Invalid emphasis value \"$val\". Must be one of: " . implode(',',$valid));
    //    }
    //
    //    // @todo Underline actually has other attributes
    //    //       (color, themeColor, themeShade, themeTint)
    //    return $this->appendSimpleValue($node, $val);
    //}
}
