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

$sec[] = new Paragraph("Simple Table");

// Table 1:
// ----------------------
// | R1C1 | R1C2 | R1C3 |
// ----------------------
// | R2C1 | R2C2 | R2C3 |
// ----------------------
$sec[] = Table::create()
    ->prop(array('align' => 'center', 'width' => '50%', 'border' => 1))
    ->row()
        ->cell('R1C1')
        ->cell('R1C2')
        ->cell('R1C3')
    ->row()
        ->cell('R2C1')
        ->cell('R2C2')
        ->cell('R2C3')
    ->end();

$sec[] = new Paragraph("Complex table with merged columns and rows");

// Table 2:
// ----------------------------
// | one       | <img> | four |
// |-----------|       |------|
// | one | two |       | four |
// |     |-------------|------|
// |     | two         | four |
// ----------------------------
$sec[] = Table::create()
    ->prop(array('align' => 'center', 'width' => '50%', 'border' => 1))
    ->row()
        ->cell('one')->colspan(2)
        ->cell(new Image('http://php.net/images/php.gif', array('align' => 'center', 'spacing' => 0)))->rowspan(2)
        ->cell('four')
    ->row()
        ->cell('one')->rowspan(2)
        ->cell('two')
        //->merged()    // not required if ->end() is called
        ->cell('four')
    ->row()
        //->merged()    // not required if ->end() is called
        ->cell('two')->colspan(2)
        ->cell('four')
    ->end();

$sec[] = new Paragraph("Table with skipped columns");

// Table 3:
//       ----------------------
//       | two | three | four |
// ----------------------------
// | one | two |
// ----------------------------
//       | two | three | four |
//       ----------------------
$sec[] = Table::create()
    ->prop(array('align' => 'center', 'width' => '50%', 'border' => 1))
    ->grid()    // a grid must be defined when you want to skip columns
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
        ->cell('one')
        ->cell('two')
        ->skipAfter(2)
    ->row()
        ->skipBefore(1)
        ->cell('two')
        ->cell('three')
        ->cell('four')
    ->end();

// Save document as XML to STDOUT
//Writer\XML::saveDocument($doc);
Writer\Word2007::saveDocument($doc, __DIR__ . '/' . basename(__FILE__, '.php') . '.docx');
