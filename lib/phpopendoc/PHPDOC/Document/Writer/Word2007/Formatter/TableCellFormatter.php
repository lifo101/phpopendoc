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
 * Creates properties for Table <w:tbl>
 */
class TableCellFormatter extends Shared
{

    /**
     * Property aliases
     */
    private static $aliases = array(
        'width'         => 'tcW',
        'valign'        => 'valign',
        'border'        => 'tcBorders',
    );

    protected function initMap()
    {
        parent::initMap(self::$aliases);
        $this->map = array(
            'cellMerge'         => '',
            'gridSpan'          => 'decimal',
            'hMerge'            => '',
            'noWrap'            => 'bool',
            'shd'               => '',
            'tcBorders'         => 'border',
            'tcFitText'         => 'fittext',
            'tcMar'             => 'margin',
            'tcW'               => 'tblWidth',
            'textDirection'     => 'textdir',
            'vAlign'            => 'valign',
            'vMerge'            => '',
        ) + $this->map;
    }

    /**
     * Process border property
     */
    protected function process_border($name, $val, ElementInterface $element, \DOMNode $root)
    {
        static $sides = array('top', 'right', 'bottom', 'left',
                              'insideH', 'insideV', 'tl2br', 'tr2bl');
        static $attrs = array('val', 'color', 'themeColor', 'themeTint',
                              'themeShade', 'sz', 'space', 'shadow', 'frame');

        $dom = $root->ownerDocument;
        $prop = $dom->createElement('w:' . $name);

        // If $val is a string then copy its value to each border side
        if (!is_array($val)) {
            // @todo make this smarter
            $val = array(
                'top' => $val,
                'right' => $val,
                'bottom' => $val,
                'left' => $val,
                'insideV' => $val,
                'insideH' => $val
            );
        }

        foreach ($val as $side => $bdr) {
            if (!in_array($side, $sides)) {
                continue;
            }

            $node = $dom->createElement('w:' . $side);
            $prop->appendChild($node);

            // @todo make this smarter
            if (!is_array($bdr)) {
                $bdr = array( 'sz' => $bdr );
            }

            // WordML requires the 'val' attribute to be present
            if (!isset($bdr['val'])) {
                $bdr['val'] = 'single';
            }

            foreach ($bdr as $k => $v) {
                if (!in_array($k, $attrs)) {
                    continue;
                }

                if ($k == 'sz') {
                    $v = $v * 8;               // Eighths of a point
                } elseif ($k == 'shadow') {
                    $v = $this->getOnOff($v);
                }

                $node->appendChild(new \DOMAttr('w:' . $k, $v));
            }
        }

        $root->appendChild($prop);
        return true;
    }

}
