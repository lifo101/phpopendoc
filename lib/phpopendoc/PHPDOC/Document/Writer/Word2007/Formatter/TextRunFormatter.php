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
        'font'          => 'rFonts',
    );

    protected function initMap()
    {
        parent::initMap(self::$aliases);
        $this->map = array(
            'b'                     => 'bool',
            'bdr'                   => 'border',
            'caps'                  => 'caps',
            'color'                 => 'text',      // @todo change to "color"
            'dstrike'               => 'bool',
            'effect'                => 'text',
            'em'                    => 'emphasis',
            'emboss'                => 'bool',
            'fitText'               => 'fittext',
            'highlight'             => 'text',
            'i'                     => 'bool',
            'imprint'               => 'bool',
            'kern'                  => 'kern',      // hps measurement
            'noProof'               => 'bool',
            'outline'               => 'bool',
            'position'              => 'position',  // hps measurement
            'rFonts'                => 'font',
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
            'u'                     => 'underline',
            'vanish'                => 'bool',
            'vertAlign'             => 'text',
            'w'                     => 'decimal',   // actually a percent 0-600%
            'webHidden'             => 'bool',
        ) + $this->map;
    }

    protected function process_size($name, $val, ElementInterface $element, \DOMNode $root)
    {
        return $this->appendSimpleValue($root, $name, Translator::pointToHalfPoint($val));
    }

    protected function process_spacing($name, $val, ElementInterface $element, \DOMNode $root)
    {
        // @todo this isn't very smart ...
        // if $val is an array then it was bubbled up from its paragraph parent
        if (is_array($val)) {
            return false;
        }
        return $this->appendSimpleValue($root, $name, Translator::pointToTwip($val));
    }

    protected function process_underline($name, $val, ElementInterface $element, \DOMNode $root)
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
        return $this->appendSimpleValue($root, $name, $val);
    }

    protected function process_emphasis($name, $val, ElementInterface $element, \DOMNode $root)
    {
        static $valid = array(
            'none', 'dot', 'comma', 'circle', 'underDot'
        );

        if ($val === true) {
            $val = 'dot';
        }
        if (!in_array($val, $valid)) {
            throw new SaveException("Invalid emphasis value \"$val\". Must be one of: " . implode(',',$valid));
        }

        return $this->appendSimpleValue($root, $name, $val);
    }
}
