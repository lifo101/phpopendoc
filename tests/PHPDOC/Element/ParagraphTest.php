<?php

use PHPDOC\Document,
    PHPDOC\Element\Paragraph,
    PHPDOC\Element\Text,
    PHPDOC\Element\TextRun,
    PHPDOC\Property\Properties
    ;

class ParagraphTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     */
    public function testParagraph()
    {
        $par = new Paragraph('test');
        $this->assertTrue($par->hasElements(), '->hasElements() returns true');

        $text = new Text('test');
        $run = new TextRun('test');
        $par = new Paragraph(array($text, $run));
        $actual = $par->getElements();
        $this->assertInstanceOf('PHPDOC\\Element\\TextRunInterface', $actual[0], 'new accepted Text() instance');
        $this->assertSame($run, $actual[1], 'new accepted TextRun() instance');
        
        $par = new Paragraph(array('test1', 'test2'));
        $this->assertCount(2, $par->getElements(), '->getElements() returns array');

        $par = new Paragraph('test', array('bold' => true));
        $this->assertTrue($par->hasProperties(), '->hasProperties() returns true');

        $prop = new Properties(array('bold' => true));
        $par = new Paragraph('test', $prop);
        $this->assertTrue($par->hasProperties(), '->hasProperties() returns true (PropertiesInterface)');
        $this->assertSame($prop, $par->getProperties(), '->getProperties() returns Properties');
    }
    
    /**
     * @expectedException UnexpectedValueException
     */
    public function testException()
    {
        $par = new Paragraph(new \DateTime()); // throws UnexpectedValueException
    }

}
