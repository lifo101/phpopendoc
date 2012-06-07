<?php

use PHPDOC\Document,
    PHPDOC\Element\Text
    ;

class TextTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     */
    public function testText()
    {
        $text = new Text();
        $this->assertNull($text->getContent(), '->getContent() is null');

        $text = new Text('test');
        $this->assertEquals('test', $text->getContent(), '->getContent() is valid');

        $text = new Text();
        $text->setContent('test');
        $this->assertEquals('test', $text->getContent(), '->set() is valid');

        $text = new Text();
        $this->assertFalse($text->hasContent(), '->has() returns false');
        $text->setContent('test');
        $this->assertTrue($text->hasContent(), '->has() returns true');
        
        $this->assertFalse($text->hasElements(), '->hasElements() must return false');
        $this->assertEquals(array(), $text->getElements(), '->getElements() must return empty array');
    }

}
