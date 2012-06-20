<?php
/**
 * This example shows how to create a table.
 * 
 */

require __DIR__ . '/autoload.php';

use PHPDOC\Document,
    PHPDOC\Document\Writer,
    PHPDOC\Property\Properties,
    PHPDOC\Element\Paragraph,
    PHPDOC\Element\Image,
    PHPDOC\Element\Text,
    PHPDOC\Element\Table
    ;

// start a new document
$doc = new Document();
$sec = $doc->addSection();

$sec[] = new Paragraph("Table Example", array('style' => 'Title'));

// Table 1:
// ----------------------
// | R1C1 | R1C2 | R1C3 |
// ----------------------
// | R2C1 | R2C2 | R2C3 |
// ----------------------
$sec[] = Table::create()
    ->row()
        ->cell('R1C1')
        ->cell('R1C2')
        ->cell('R1C3')
    ->row()
        ->cell('R2C1')
        ->cell('R2C2')
        ->cell('R2C3')
    ;

// Table 2:
// ---------------------
// | one | two | <img> |
// ------------|       |
// | three     |       |
// ---------------------
$sec[] = Table::create()
    ->row()
        ->cell("one")
        ->cell("two")
        ->cell(new Image('http://php.net/images/php.gif'), array('rowspan' => 2))
    ->row()
        ->cell("three", array('colspan' => 2))
    ;

// Table 3:
//       ----------------------
//       | two | three | four |
// ----------------------------
// | one | two |
// -------------
$sec[] = Table::create()
    ->grid()
        ->col(1000)
        ->col(1000)
        ->col(1000)
        ->col(1000)
    ->row()
        ->skipBefore(1)
        ->cell('two')
        ->cell('three')
        ->cell('four')
    ->row()
        ->cell("one")
        ->cell("two")
        ->skipAfter(2)
    ;

// Save document as XML to STDOUT
Writer\XML::saveDocument($doc);
