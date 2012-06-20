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
    PHPDOC\Element\ElementInterface,
    PHPDOC\Element\Paragraph as BlockParagraph
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

        foreach ($element->getElements() as $row) {
            // skip any rows that have no cells
            //if (!$row['cells']) {
            //    continue;
            //}
            
            $tr = $writer->getDom()->createElement('tr');
            $table->appendChild($tr);

            if (isset($row['properties'])) {
                foreach ($row['properties'] as $key => $val) {
                    $tr->appendChild(new \DOMAttr($key, $val));
                }
            }

            foreach ($row['cells'] as $cell) {
                $td = $writer->getDom()->createElement('td');
                $tr->appendChild($td);

                if (isset($cell['properties'])) {
                    foreach ($cell['properties'] as $key => $val) {
                        $td->appendChild(new \DOMAttr($key, $val));
                    }
                }

                foreach ($cell['elements'] as $element) {
                    // All cell content must be in a block level element
                    //if (!($element instanceof BlockInterface)) {
                    //    $element = new BlockParagraph($element);
                    //}
                    $writer->processElement($td, $element);
                }
            }
        }
    }
}
