<?php

use PHPDOC\Document,
    PHPDOC\Element\Link,
    PHPDOC\Element\Text,
    PHPDOC\Element\TextRun,
    PHPDOC\Property\Properties
    ;

class TextRunTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     */
    public function testTextRun()
    {
        $run = new TextRun('test');
        $this->assertTrue($run->hasElements(), '->hasElements() returns true');

        $run = new TextRun(array('test1', 'test2'));
        $this->assertCount(2, $run->getElements(), '->getElements() returns array');
        
        $run = new TextRun('test', array('bold' => true));
        $this->assertTrue($run->hasProperties(), '->hasProperties() returns true');

        $prop = new Properties(array('bold' => true));
        $run = new TextRun('test', $prop);
        $this->assertTrue($run->hasProperties(), '->hasProperties() returns true (PropertiesInterface)');
        $this->assertSame($prop, $run->getProperties(), '->getProperties() returns Properties');
        
        $text = new Text('test', array('bold' => true));
        $run = new TextRun($text);
        $this->assertTrue($run->getProperties()->has('bold'), 'TextRun copied Text properties');
    }

    /**
     * @expectedException PHPDOC\Element\ElementException
     */
    public function testLinkException()
    {
        $run = new TextRun( new Link('a link') );
    }
}
