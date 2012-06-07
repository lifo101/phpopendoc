<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 * 
 */
namespace PHPDOC\Document\Writer\XML;

use PHPDOC\Document\WriterInterface,
    PHPDOC\Element\ElementInterface;

abstract class Text // implements ElementInterface
{
    public static function process(WriterInterface $writer, \DOMNode $root, ElementInterface $element)
    {
        $node = $writer->getDom()->createElement('t', $element->getContent());
        $root->appendChild($node);
    }
}
