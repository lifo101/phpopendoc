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
class TableFormatter extends Shared
{
    
    /**
     * Property aliases
     */
    private static $aliases = array(
        'align'     => 'jc',
        'justify'   => 'jc',
        'width'     => 'tblW',
    );
    
    protected function initMap()
    {
        parent::initMap(self::$aliases);
        $this->map = array(
            'bidiVisual'            => '',
            'jc'                    => 'align',
            'shd'                   => 'shd',
            'tblBorders'            => 'border',
            'tblCellMar'            => 'margin',
            'tblCellSpacing'        => 'spacing',
            'tblInd'                => '',
            'tblLayout'             => '',
            'tblLook'               => '',
            'tblOverlap'            => '',
            'tblpPr'                => '',
            'tblStyle'              => '',
            'tblStyleColBandSize'   => '',
            'tblStyleRowBandSize'   => '',
            'tblW'                  => 'tblWidth',
        ) + $this->map;
    }

}
