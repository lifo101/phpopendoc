<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 *
 */
namespace PHPDOC\Element;

/**
 * BreakChar element represents a single line break in the document. Similar
 * to <br/> in HMTL.
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */

class Br extends Character
{
    protected $type;
    protected $clear;

    /**
     * Constructor
     *
     * $type is optional and if a $clear value is given it will be used
     * apropriately.
     *
     * @param string $type  Type of break; can be omitted
     * @param string $clear Type of clear
     */
    public function __construct($type = null, $clear = null)
    {
        static $types = array('page', 'column', 'textWrapping');
        static $clears = array('none', 'left', 'right', 'all');

        // $type is optional and if it has a $clear value then swap
        if (in_array($type, $clears)) {
            $clear = $type;
            $type = null;
        }
        if ($type == null or $type == 'text') {
            $type = 'textWrapping';
        }

        $this->type = $type;
        $this->clear = $clear;
    }

}
