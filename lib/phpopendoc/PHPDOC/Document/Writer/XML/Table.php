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
    PHPDOC\Element\BlockInterface,
    PHPDOC\Element\ElementInterface
    ;

abstract class Table
{
    public static function process(WriterInterface $writer, \DOMNode $root, ElementInterface $element)
    {
        $table = $writer->getDom()->createElement('table');
        $root->appendChild($table);

        if ($element->hasProperties()) {
            foreach ($element->getProperties() as $key => $val) {
                $table->appendChild(new \DOMAttr($key, $val));
            }
        }

        foreach ($element->getRows() as $row) {
            $tr = $writer->getDom()->createElement('tr');
            $table->appendChild($tr);

            if ($row->hasProperties()) {
                foreach ($row->getProperties() as $key => $val) {
                    $tr->appendChild(new \DOMAttr($key, $val));
                }
            }

            foreach ($row->getElements() as $cell) {
                $td = $writer->getDom()->createElement('td');
                $tr->appendChild($td);

                if ($cell->hasProperties()) {
                    foreach ($cell->getProperties() as $key => $val) {
                        $td->appendChild(new \DOMAttr($key, $val));
                    }
                }

                foreach ($cell->getElements() as $element) {
                    // All cell content must be in a block level element
                    //if (!($element instanceof BlockInterface)) {
                    //    $element = new Element\Paragraph($element);
                    //}
                    $writer->processElement($td, $element);
                }
            }
        }
    }
}
