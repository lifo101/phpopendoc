<?php
/**
 * This simple example shows how to create a minimal document with a couple of
 * styles and save it.
 *
 */

require __DIR__ . '/autoload.php';

use PHPDOC\Document,
    PHPDOC\Document\Writer,
    PHPDOC\Element\Paragraph,
    PHPDOC\Element\Text,
    PHPDOC\Style\ParagraphStyle,
    PHPDOC\Style\TextStyle
    ;

// start a new document; and define some default styles
$doc = new Document(array(
    'defaultStyles' => array(
        'text' => array(
            'size' => 10,   // measured in points
            'font' => 'Arial',
        ),
        // default paragraph styles can be defined too
        //'paragraph' => array(...),
    )
));

// Create some styles
$doc[] = new ParagraphStyle('Title', array(
    'border.bottom.sz' => 1,
    'border.bottom.color' => '000077',
    'size' => 20,
    'font' => 'Cambria',
    'spacing.after' => 240
));

$doc[] = new TextStyle('Important', array(
    'color' => 'ff0000',
    'font' => 'Elephant',
));

// start a new section (page)
$sec = $doc->addSection();

$sec[] = new Paragraph("Hello World", "Title");

$sec[] = new Paragraph(array(
    "This is a paragraph with some default text. ",
    "However, the next word has the \"important\" style applied to it: ",
    new Text("important! ", "Important"),
    "And this is just more text. ",
    "Here is another important word: ",
    new Text("PHPOpenDoc", array('style' => 'Important', 'noProof' => true)),
    "."
));

$sec[] = new Paragraph("Another Title", "Title");
$sec[] = "Changing the \"Title\" style will cause all Titles within this "
        . "document to update automatically!";

$head = $sec->addHeader();
$head[] = new Text("PHPOpenDoc example " . basename(__FILE__),
                   array('align' => 'center', 'size' => 9, 'noProof' => true));

// Save the document
Writer\Word2007::saveDocument($doc, __DIR__ . '/' . basename(__FILE__, '.php') . '.docx');
