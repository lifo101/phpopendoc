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
$sec = $doc->addSection();

// Add some text ...
//$sec[] = "String";
//$sec[] = array("Array", "Array2");
//$sec[] = new Text("Text");
//$sec[] = new TextRun("TextRun");
//$sec[] = new Paragraph("Paragraph");
$sec[] = "The quick brown fox jumped over the lazy dog.";

print_r($doc->getSections());

//print Writer\XML::save($doc);
//$xml = new Writer\XML($doc);
//print $xml->save();
// or
//print Writer\XML::save($doc);