<?php

use PHPDOC\Property\Properties;

/**
 * @covers PHPDOC\Property\PropertiesInterface
 */
class PropertiesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers PHPDOC\Property\Properties::__construct
     */
    public function testConstructor()
    {
        $prop = new Properties(array('foo' => 'bar'));
        $this->assertInstanceOf('PHPDOC\Component\PropertyBag', $prop, 'new Properties() is an instance of PropertyBag');
        $this->assertInstanceOf('PHPDOC\Property\PropertiesInterface', $prop, 'new Properties() is an instance of PropertiesInterface');

        // verify Properties() can take another Properties instance properly
        $prop2 = new Properties( $prop );
        $this->assertTrue($prop2->has('foo'), 'new Properties($prop) can take a PropertiesInterface');
        $this->assertNotSame($prop, $prop2, 'new Properties() returned unique object');
    }
 
    /**
     * @covers PHPDOC\Property\Properties::set
     */
    public function testSet()
    {
        $prop = new Properties();
        $prop->set('foo', 'bar');
        $this->assertEquals('bar', $prop->get('foo'), '->set() assigned property');
        
        $expected = array('foo' => 'bar', 'baz' => 'qux');
        $prop = new Properties();
        $prop->set($expected);
        $this->assertEquals($expected, $prop->all(), '->set() assigned properties via array');
    }

    /**
     * @covers PHPDOC\Property\Properties::hasProperties
     */
    public function testHasProperties()
    {
        $prop = new Properties();
        $this->assertFalse($prop->hasProperties(), '->hasProperties() returns false');

        $prop = new Properties(array('foo' => 'bar', 'baz' => 'qux'));
        $this->assertTrue($prop->hasProperties(), '->hasProperties() returns true');
    }
}