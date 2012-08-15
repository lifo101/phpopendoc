<?php
/**
 * This example shows how to create a list.
 *
 */

require __DIR__ . '/autoload.php';

use PHPDOC\Document,
    PHPDOC\Document\Writer,
    PHPDOC\Property\Properties,
    PHPDOC\Style\ParagraphStyle,
    PHPDOC\Element\TextRun,
    PHPDOC\Element\Paragraph,
    PHPDOC\Element\Table,
    PHPDOC\Element\ListItems
    ;

// start a new document
$doc = new Document(array(
    'interpolate_fields' => true,
    'defaultStyles' => array(
        'paragraph' => array(
            'spacing.after' => 240,
            'spacing.line' => 240 * 1.15,
        ),
        'text' => array(
            'size' => 11,
            'font' => 'Calibri',
        ),
    )
));

// You'll probably usually want to create a style for the list(s) you are going
// to create.
$doc[] = new ParagraphStyle('List Paragraph', array(
    'next' => 'ListParagraph',
    'indent.hanging' => 0.25,
    'contextualSpacing' => true,
    'keepNext' => true,
));

$doc[] = new ParagraphStyle('Title', array(
    'border.bottom.sz' => 1,
    'size' => 14,
    'font' => 'Cambria',
    'spacing.after' => 240
));

$sec = $doc->addSection();
$sec[] = new Paragraph("Simple Bullet List", "Title");
// Creating a list is very similar to creating a Table
$sec[] = ListItems::create(0, "ListParagraph")
    ->item("Item 1")
    ->item("Item 2")
    ->item(new TextRun("Another Item", array('b' => true, 'color' => '008800')))
    ->item("Last Item");

$sec[] = new Paragraph("Another Simple List", "Title");
$sec[] = ListItems::create(3, "ListParagraph")
    ->item("Item 1")
    ->listItems()
        ->item("Item 2")
        ->listItems()
            ->item("Item 3")
            ->listItems()
                ->item("Item 4")
            ->end()
        ->end()
    ->end();

$sec[] = new Paragraph("Nested Numbered (Hybrid) List", "Title");
$sec[] = ListItems::create(1, "ListParagraph")
    ->item("Level 1")
    ->listItems()
        ->item("Level 2")
        ->listItems()
            ->item("Level 3")
        ->end()
        ->item("Level 2")
    ->end();

Writer\Word2007::saveDocument($doc, __DIR__ . '/' . basename(__FILE__, '.php') . '.docx');
