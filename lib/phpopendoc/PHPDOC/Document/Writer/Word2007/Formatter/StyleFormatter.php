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
 * Creates properties for styles <w:style>
 */
class StyleFormatter extends Shared
{
    /**
     * Property aliases
     */
    private static $aliases = array(
        'alias'                 => 'aliases',
        'primary'               => 'qFormat',
        'quick'                 => 'qFormat',
        'unhide'                => 'unhideWhenUsed',
    );

    protected function initMap()
    {
        parent::initMap(self::$aliases);
        $this->map = array(
            'aliases'           => 'aliases',
            'basedOn'           => 'text',
            'hidden'            => 'bool',
            'link'              => 'text',
            'locked'            => 'bool',
            'name'              => 'text',
            'next'              => 'text',
            'personal'          => 'bool',
            'personalCompose'   => 'bool',
            'personalReply'     => 'bool',
            'qFormat'           => 'bool',
            'semiHidden'        => 'bool',
            'uiPriority'        => 'decimal',
            'unhideWhenUsed'    => 'bool',
        );
    }

    protected function process_aliases($name, $val, ElementInterface $element, \DOMNode $root)
    {
        return $this->process_text($name, is_array($val) ? implode(',',$val) : $val, $element, $root);
    }
}
