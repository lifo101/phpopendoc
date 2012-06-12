# PHP Open Doc

A PHP library for creating "Office Open XML" documents which are compatible with
Microsoft Word 2007+ (docx format). Documents may also be saved as simple XML
structures. The XML output could be read by other XML consumers or used in XSLT
tranformations.

__This library is in early development and does not actually work yet.__

## Synopsis

I needed a non-windows solution for creating Word documents ("docx" files) and
got tired of the lack of support, limited API and the countless bugs in existing
projects that I've found. So I started work on this project. I wasn't sure what
name to give it, so for now "PHP Open Doc" will suffice, but may change in the
future.

I wanted a library that gave me a clear and easy API to create documents using a
proper object oriented interface and that offered flexibility with more than one
way to do certain things. I also wanted the API to be have fully automated unit
tests and have 100% code coverage.

## Requirements

* [PHP v5.3+](http://php.net/)
* [ZipArchive](http://php.net/manual/en/class.ziparchive.php) (PHP zip extension "_--enable-zip_")
* [DOMDocument](http://php.net/manual/en/class.domdocument.php) (PHP dom extension)

## Features

* Uses [namespaces](http://php.net/manual/en/language.namespaces.php)
* Easy and flexible API
* Save documents as [WordML](http://en.wikipedia.org/wiki/Office_Open_XML) (MS Word "docx") or plain XML
* Uhhh... and other stuff... Too early to list stuff here...

## Examples

_Note: The API is still unstable and **WILL** change._

```php
<?php
use PHPDOC\Document;
use PHPDOC\Document\Writer;
use PHPDOC\Element\Paragraph;
use PHPDOC\Element\Text;
use PHPDOC\Element\Image;

$doc = new Document;
$sec = $doc->addSection();
$sec[] = new Paragraph(array(
    'The quick brown fox ...',
    new Text('This sentence is bolded.', array('bold' => true))),
    'Here is an image: ',
    new Image('/path/to/fox.png')
));
$sec[] = "This is another sentence, inside separate paragraph.";
$sec[] = new Text("Here is one more that is green...", array('color' => '00DD00'));

// The Section variable is not just a normal array
$sec->set("Just another sentence"); 

$sec->addHeader()->set("My Header");

Writer\XML::saveDocument($doc); // output to STDOUT

// .... API is still __extremely__ unstable

?>
```
