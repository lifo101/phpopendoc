<?php
/**
 * This simple example shows the minimal steps required to create a basic
 * "Hello World" document and save it.
 * 
 */

require __DIR__ . '/autoload.php';

use PHPDOC\Document,
    PHPDOC\Document\Writer;

// start a new document
$doc = new Document();

// Add a single paragraph to a section in the document
$sec = $doc->addSection();
$sec[] = "Hello World";

// Save document as XML to STDOUT
Writer\XML::saveDocument($doc);