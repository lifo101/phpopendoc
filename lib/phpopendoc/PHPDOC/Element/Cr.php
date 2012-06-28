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
 * Cr element represents a single carriage return in the document. Similar
 * to <br/> in HMTL.
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */

class Cr extends Br
{
    public function __construct()
    {
        $this->type = null;
        $this->clear = null;
    }
}
