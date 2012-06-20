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
    PHPDOC\Document\Writer\Exception\SaveException
    ;

/**
 * Creates properties for paragraphs <w:p>
 */
class Paragraph extends Shared
{
    
    /**
     * Property map
     */
    private static $propertyMap = array(
        'align'     => 'jc',
        'justify'   => 'jc',
        'jc'        => 'jc',
    );
    
    public function __construct()
    {
        parent::__construct(self::$propertyMap);
    }

    protected function translate_jc($name, $val, $node)
    {
        static $valid = array(
            'both', 'justify', 'right', 'center', 'distribute',
            'highKashida', 'lowKashida', 'mediumKashida', 'thaiDistribute'
        );
        
        if ($val == 'justify') {
            $val = 'both';
        }
        if (!in_array($val, $valid)) {
            throw new SaveException("Invalid justify value \"$val\". Must be one of: " . implode(',',$valid));
        }
        
        return $this->appendSimpleValue($node, $val);
    }

}
