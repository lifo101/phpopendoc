<?php

require __DIR__ . '/autoload.php';

use PHPDOC\Document,
    PHPDOC\Property,
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

//$prop = new Property\TextRunProperty(array('bold' => true));
//$prop['size'] = 12;
//$prop['spacing'] = 10;
//print "$prop\n";
////print_r($prop->all());
////foreach ($prop as $k => $v) {
////    print "$k = $v\n";
////}
//exit;

// add a paragraph to the section with 0 or more "Text Runs"
$p = new Paragraph(array(
    'The quick ',
    new TextRun('brown', array('bold' => true)),
    ' fox jumped over the lazy dog.'
));
print $p;

//Each line below does the same thing
//$sec->addText('Text goes here');
//$sec[] = 'Text goes here';
//$sec[] = new Element\Text('Text goes here');

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


