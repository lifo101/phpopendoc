<?php

use PHPDOC\Element\Section,
    PHPDOC\Element\HeaderFooter
    ;

class HeaderFooterTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * @covers PHPDOC\Element\HeaderFooter::__construct
     * @covers PHPDOC\Element\HeaderFooter::getType
     * @covers PHPDOC\Element\HeaderFooter::getPosition
     */
    public function testHeaderFooter()
    {
        $h = new HeaderFooter();
        $this->assertInstanceOf('PHPDOC\\Element\\HeaderFooterInterface', $h, 'HeaderFooter is an instance of HeaderFooterInterface');
        $this->assertEquals('header', $h->getPosition(), '->getPosition() returns "header"');
        $this->assertEquals('default', $h->getType(), '->getType() returns "default"');

        $h = new HeaderFooter('footer', 'odd');
        $this->assertEquals('footer', $h->getPosition(), '->getPosition() returns "footer"');
        $this->assertEquals('odd', $h->getType(), '->getType() returns "odd"');
        
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testPropertiesException()
    {
        $h = new HeaderFooter('bad value');   // throws \UnexpectedValueException
    }
    
}
