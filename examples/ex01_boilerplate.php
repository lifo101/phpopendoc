<?php

require __DIR__ . '/autoload.php';

use PHPDOC\Document,
    PHPDOC\Element\Section,	
    PHPDOC\Element\Text,
    PHPDOC\Element\TextRun,
    PHPDOC\Element\Paragraph
    ;

$doc = new Document();

// set global document properties
$doc->setProperties(array(
    // ...	
));

// start a new section
// (name argument is optional)
$sec = $doc->addSection('page0');

// add a paragraph to the section with 0 or more "Text Runs"
$p = new Paragraph(array(
    'The quick brown fox ',
    'jumped over the lazy dog.',
    new Text('The quick brown fox ...'),
    new TextRun(array('The ', 'quick ', 'brown ', 'fox ... '), array('bold' => true)),
));
$p->setProperties(array(
    'bold' => true,
    'italic' => true,
));
print $p;

 //Each line below does the same thing
$sec->addText('Text goes here');
$sec[] = 'Text goes here';
$sec[] = new Element\Text('Text goes here');

// Add a table
//$sec[] = Element\Table::create()
//    ->addRow()
//        ->addCell('R1C1')
//        ->addCell(new Element\Text('R1C2'))
//        ->addCell('R1C3')
//    ->addRow()
//        ->addCell('R2C1')
//        ->addCell('R2C2')
//        ->addCell('R2C3')
//    ;
//
//// Add an image
//$sec[] = new Element\Image('/path/to/image.png');
//$sec[] = new Element\Image(array('src' => '/path/to/image.png'));


