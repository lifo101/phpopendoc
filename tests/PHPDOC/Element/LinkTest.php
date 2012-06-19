<?php

use PHPDOC\Document,
    PHPDOC\Element\Link
    ;

class LinkTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * @covers PHPDOC\Element\Link::__construct
     * @covers PHPDOC\Element\Link::getInterface
     * @covers PHPDOC\Element\Link::getTarget
     * @covers PHPDOC\Element\Link::setTarget
     */
    public function testLink()
    {
        $url1 = 'http://my.domain/';
        $url2 = 'https://secure.domain/';
        $text = 'This is my link';
        
        $link = new Link($url1, $text);
        $this->assertInstanceOf('PHPDOC\\Element\\LinkInterface', $link, 'Link is an instance of LinkInterface');
        $this->assertEquals('PHPDOC\\Element\\LinkInterface', $link->getInterface(), '->getInterface() returns LinkInterface');
        
        $this->assertEquals($url1, $link->getTarget(), "->getTarget() returns URL1: $url1");
        $link->setTarget($url2);
        $this->assertEquals($url2, $link->getTarget(), "->getTarget() returns URL2: $url2");
    }

}
