<?php
/**
 * This example shows how to create a basic Document using various ways of
 * adding content. This example may be a bit overwhelming at first, so you may
 * want to look at some of the other examples that are more focused.
 *
 * This example outputs the document as XML just for testing purposes. The XML
 * is not in WordML format. It's a simple structure for testing purposes. The
 * XML could actually be traversed and used elsewhere too. Someone could even
 * use XSLT transformations on it to convert it into something else.
 */

// You could replace this with your own autoloader
require __DIR__ . '/autoload.php';

use PHPDOC\Document,
    PHPDOC\Document\Writer,
    PHPDOC\Element\Section,	
    PHPDOC\Element\Paragraph,
    PHPDOC\Element\Image,
    PHPDOC\Element\Text,
    PHPDOC\Element\TextRun
    ;

// start a new document...
$doc = new Document();

// Create a section ...
$sec = $doc->addSection('page one');

// Quick method to add paragraphs (with no formatting)
$sec[] = "The quick brown fox jumped over the lazy dog.";
$sec[] = "The early bird gets the worm.";

// More advanced method to add a paragraph that contains formatting and other
// elements.
$sec = $doc->addSection('page two');
$sec[] = new Paragraph(array(
    "This is one short sentence that has an image ",
    new Image(__DIR__ . '/../tests/res/media/earth.jpg'),
    " and has ",
    new TextRun("different styles ", array('italic' => true, 'bold' => false)),
    "set on it."
), array(
    'bold' => true,
    'spacingLeft' => 10,
    'spacingAfter' => 30
));

// add a header and footer ... Add content just like a Section
$sec->addHeader()->set(      new Text('My Header',   array('align' => 'center')));
$sec->addFooter('odd')->set( new Text('Odd footer',  array('align' => 'left')));
$sec->addFooter('even')->set(new Text('Even footer', array('align' => 'right')));

// Save the document as XML ...
Writer\XML::saveDocument($doc);     // by default output is written to STDOUT
                                    // specify a filename as 2nd param to write
                                    // to disk

// alternate method for saving the document. This allows you more control
// over how the Writer will save the document. However, In this example we
// don't do anything special.
//$xml = new Writer\XML($doc);
//$xml->save(__DIR__ . '/' . basename(__FILE__, '.php') . '.xml');

