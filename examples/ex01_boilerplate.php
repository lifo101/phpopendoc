<?php

// You could replace this with your own autoloader
require __DIR__ . '/autoload.php';

use PHPDOC\Document,
    PHPDOC\Document\Writer,
    PHPDOC\Element\Section,	
    PHPDOC\Element\Paragraph,
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

// More advanced method to add a paragraph that contains formatting
$sec = $doc->addSection('page two');
$sec[] = new Paragraph(array(
    "This is one short sentence ",
    new TextRun("with different styles ", array('italic' => true, 'bold' => false)),
    "set on it."
), array(
    'bold' => true,
    'spacingLeft' => 10,
    'spacingAfter' => 30
));

// Save the document as XML ...
$xml = new Writer\XML($doc);
$xml->setSaveException(false);  // used for developement
$xml->save();                   // by default output is written to STDOUT
                                // specify a filename to write to disk

// shortcut to save the document (as XML)
//Writer\XML::saveDocument($doc);
