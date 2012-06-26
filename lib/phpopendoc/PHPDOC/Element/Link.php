<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 *
 */
namespace PHPDOC\Element;

use PHPDOC\Property\Properties;

/**
 * The Link element class represents a single hyperlink that can include
 * 1 or more TextRun's.
 *
 * @example
 * <code>
    $p = new Paragraph(array(
        new Link('target', array(
            'This is standard ',
            new Text("TextRun", array('bold' => true)),
            ' content. Now click me!'
        ))
    ));
 * </code>
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
class Link extends Paragraph implements LinkInterface
{
    protected $target;

    public function __construct($target, $elements = null, $properties = null)
    {
        // default link text to the target if nothing is given
        if ($elements == null) {
            $elements = $target;
        }
        parent::__construct($elements, $properties);
        $this->target = $target;
    }

    public function getInterface()
    {
        return __NAMESPACE__ . '\\LinkInterface';
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function setTarget($target)
    {
        $this->target = $target;
        return $this;
    }
}
