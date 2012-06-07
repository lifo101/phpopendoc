<?php

use PHPDOC\Document,
    PHPDOC\Element\Element,
    PHPDOC\Property\Properties,
    PHPDOC\Property\PropertiesInterface
    ;

class ElementTest extends \PHPUnit_Framework_TestCase
{

    public function testElement()
    {
        $ele = new Element(array('bold' => true, 'spacing' => 10));
        $this->assertTrue($ele->hasProperties(), '->hasProperties() returns true');

        $prop = new Properties(array('bold' => true));
        $ele = new Element($prop);
        $this->assertTrue($ele->hasProperties(), '->hasProperties() returns true (PropertiesInterface)');
        $this->assertSame($prop, $ele->getProperties(), '->getProperties() returns Properties');
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetPropertiesException()
    {
        $ele = new Element(new \DateTime()); // throws InvalidArgumentException
    }

}
