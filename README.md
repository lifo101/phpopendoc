# PHP Open Doc

A PHP library for creating "Office Open XML" documents which are compatible with Microsoft Word 2007+ (docx format).

__This library is in early development and does not actually work yet.__
I work from two different computers so I may commit changes that end up breaking the library on a daily basis.

## Synopsis

I needed a non-windows solution for creating Word documents ("docx" files) and got tired of the lack of support, limited API and the countless bugs in existing projects that I've found. So I started work on this project. I wasn't sure what  name to give it, so for now "PHP Open Doc" will suffice, but may change in the future.

I wanted a library that gave me a clear API to create documents using a proper object oriented interface and that offered flexibility with more than one way to do certain things. I also wanted the API to be have fully automated unit tests and have 100% code coverage.

## Requirements

* PHP v5.3+
* ZipArchive (PHP zip extension "_--enable-zip_")

## Features

* Use of PHP5 5.3 namespace's
* Uhhh... and other stuff... Too early to list stuff here...

## Examples

_Note: The API is still unstable and __WILL__ change._

```php
use PHPDOC\Document;
use PHPDOC\Element\Paragraph;
use PHPDOC\Element\TextRun;
use PHPDOC\Element\Image;

$doc = new Document;
$sec = $doc->addSection();
$sec[] = new Paragraph(array(
    'The quick brown fox ...',
    new TextRun(array('The quick ', 'brown fox ', ' ...')),
    new Image('/path/to/fox.png')
));

// .... API is still __extremely__ unstable
```
