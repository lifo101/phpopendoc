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

}
