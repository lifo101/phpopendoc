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
class TableRowFormatter extends Shared
{

    /**
     * Property aliases
     */
    private static $aliases = array(
        'align'         => 'jc',
        'justify'       => 'jc',
        'height'        => 'trHeight',
        'spacing'       => 'tblCellSpacing',
        'skipBefore'    => 'gridBefore',
        'skipAfter'     => 'gridAfter',
        'repeat'        => 'tblHeader',
    );

    protected function initMap()
    {
        parent::initMap(self::$aliases);
        $this->map = array(
            'cantSplit'             => 'bool',
            'cnfStyle'              => 'decimal',   // bit mask string
            'gridAfter'             => 'decimal',
            'gridBefore'            => 'decimal',
            'jc'                    => 'align',
            'tblCellSpacing'        => 'tblSpacing',
            'tblHeader'             => 'bool',
            'trHeight'              => 'height',
            'wAfter'                => '',
            'wBefore'               => '',
        ) + $this->map;
    }

}
