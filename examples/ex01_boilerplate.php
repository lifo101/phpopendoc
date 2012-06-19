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
    PHPDOC\Element\Link,
    PHPDOC\Element\Text,
    PHPDOC\Element\TextRun
    ;

// start a new document...
$doc = new Document();

// Create a section (the name is optional)
$sec = $doc->addSection('page one');

// Easily add paragraphs (with no formatting).
// when you add something to a section index[] you're creating a new paragraph.
// Or call the set() method directly.
$sec[] = "The quick brown fox jumped over the lazy dog.";
$sec[] = "The early bird gets the worm.";
$sec->set("Don't look a gift horse in the mouth.");

// More advanced way to add a paragraph that contains formatting and other
// elements.
$sec = $doc->addSection('page two');
$sec[] = new Paragraph(array(
    "This is a short sentence that has an image ",
    new Image(__DIR__ . '/../tests/res/media/earth.jpg'),
    " and has ",
    new Text("different styles ", array('italic' => true, 'bold' => true)),
    "set on it. ",
    new Link("http://somewebsite/", "And here is a link."),
), array(
    'align' => 'justify'    // paragraph level formatting
));

// add header/footers ... Here I add a simple text strings but you can also add
// almost any content you would normally add to a Section as well.
$sec->addHeader()->set("My Header");
$sec->addFooter('odd')->set("Odd page header");
$sec->addFooter('even')->set("Even page header");

// Save the document ...
// by default output is written to STDOUT (php:://output).
// Specify a filename as 2nd parameter to write somewhere else instead.
Writer\XML::saveDocument($doc);                             // save as XML
//Writer\Word2007::saveDocument($doc, 'boilerplate.docx');  // save as MS Word2007
