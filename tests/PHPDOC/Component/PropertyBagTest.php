<?php

use PHPDOC\Component\PropertyBag;

class PropertyBagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers PHPDOC\Component\PropertyBag::__construct
     * @covers PHPDOC\Component\PropertyBag::all
     */
    public function testAll()
    {
        $bag = new PropertyBag();
        $this->assertEmpty($bag->all(), 'Constructor accepts null');

        $ary = array('foo' => 'bar', 'nested' => array('hello.nelly' => 'world'));
        $expected = array('foo' => 'bar', 'nested' => array('hello' => array('nelly' => 'world')));
        $bag = new PropertyBag($ary);
        $this->assertEquals($expected, $bag->all(), 'Constructor accepts array');

        $prop1 = new PropertyBag($ary);
        $bag = new PropertyBag($prop1);
        $this->assertEquals($expected, $bag->all(), 'Constructor accepts PropertyBag instance');
        
    }
    
    /**
     * @covers PHPDOC\Component\PropertyBag::set
     * @covers PHPDOC\Component\PropertyBag::offsetSet
     * @covers PHPDOC\Component\PropertyBag::_getArrayRef
     * @covers PHPDOC\Component\PropertyBag::__call
     */
    public function testSet()
    {
        $bag = new PropertyBag();
        $bag->set('foo', 'bar');
        $this->assertEquals('bar', $bag->get('foo'), '->set() assigned value properly');

        $bag = new PropertyBag();
        $bag['foo'] = 'bar';
        $this->assertEquals('bar', $bag->get('foo'), '$bag[] assigned value properly (\ArrayAccess)');

        $bag = new PropertyBag();
        $bag['foo'] = 'bar';
        $bag['foo.newkey'] = 'value';
        $this->assertEquals('value', $bag->get('foo.newkey'), '$bag[] assigned nested value properly (\ArrayAccess)');

        $bag = new PropertyBag();
        $bag->setfoo('bar');
        $this->assertEquals('bar', $bag->get('foo'), '->setfoo() assigned value properly (__call magic)');

        $bag = new PropertyBag();
        $bag->set('foo.bar', 'baz');
        $expected = array( 'foo' => array( 'bar' => 'baz' ));
        $this->assertEquals($expected, $bag->all(), '->set() assigned nested value properly');

    }

    /**
     * @covers PHPDOC\Component\PropertyBag::__call
     * @expectedException InvalidArgumentException
     */
    public function testSetInvalidArgumentException()
    {
        $bag = new PropertyBag();
        $bag->setfoo();     // throws InvalidArgumentException
    }

    /**
     * @covers PHPDOC\Component\PropertyBag::__call
     * @expectedException BadMethodCallException
     */
    public function testSetBadMethodCallException()
    {
        $bag = new PropertyBag();
        $bag->badFunctionNameThatDoesNotExist();     // throws BadMethodCallException
    }

    /**
     * @covers PHPDOC\Component\PropertyBag::remove
     * @covers PHPDOC\Component\PropertyBag::offsetUnset
     */
    public function testRemove()
    {
        $bag = new PropertyBag(array('foo' => 'bar', 'baz' => 'zeek'));
        $bag->remove('foo');
        $this->assertEquals(array('baz' => 'zeek'), $bag->all(), '->remove() removed property');
    
        $bag = new PropertyBag(array('foo' => 'bar', 'baz' => 'zeek'));
        unset($bag['foo']);
        $this->assertEquals(array('baz' => 'zeek'), $bag->all(), 'unset($bag[...]) removed property');
    
        // @todo Once PropertyBag supports nested keys this needs to be refactored
        //$bag = new PropertyBag(array('foo' => array('bar' => 'zeek'), 'baz' => 'zeek'));
        //$bag->remove('foo.bar');
        //$this->assertEquals(array('baz' => 'zeek'), $bag->all(), '->remove() removed property (for nested variable)');
    }
    
    /**
     * @covers PHPDOC\Component\PropertyBag::remove
     * @expectedException InvalidArgumentException
     */
    public function testRemoveException()
    {
        // @todo Once PropertyBag supports nested keys this needs to be refactored
        $bag = new PropertyBag();
        $bag->remove('foo.bar');    // throws InvalidArgumentException
    }

    /**
     * @covers PHPDOC\Component\PropertyBag::getIterator
     */
    public function testIterator()
    {
        $ary = array('foo' => 'bar', 'baz' => 'zeek');
        $bag = new PropertyBag($ary);
        
        $this->assertTrue($bag->getIterator() instanceof \ArrayIterator, "->getIterator() is instance of \ArrayIterator");
        
        $ary2 = array();
        foreach ($bag as $key => $val) {	// cause getIterator to be called
            $ary2[$key] = $val;
        }
        $this->assertEquals($ary2, $ary, '$bag getIterator works');
    }

    /**
     * @covers PHPDOC\Component\PropertyBag::has
     * @covers PHPDOC\Component\PropertyBag::offsetExists
     * @covers PHPDOC\Component\PropertyBag::get
     * @covers PHPDOC\Component\PropertyBag::offsetGet
     * @covers PHPDOC\Component\PropertyBag::_getArrayRef
     */
    public function testHasGet()
    {
        $nested = array('bar' => 'baz', 'baz' => array('hello' => 'world'));
        $ary = array('foo' => $nested);
        $bag = new PropertyBag($ary);
        
        $this->assertNull($bag->get('does_not_exist'), '->get() returns null');
        $this->assertNull($bag->get('does_not_exist.foo.bar'), '->get() returns null');
        $this->assertNull($bag->get('foo.does.not.exist'), '->get() returns null (for nested variable)');
        $this->assertEquals('default', $bag->get('does_not_exist', 'default'), '->get() returns default');
        $this->assertEquals('default', $bag->get('foo.does.not.exist', 'default'), '->get() returns default (for nested variable)');
        $this->assertEquals($nested, $bag->get('foo'), '->get() returns nested array');
        $this->assertEquals($nested['bar'], $bag->get('foo.bar'), '->get() returns nested variable');

        $this->assertTrue($bag->has('foo'), '->has() returns true (foo)');
        $this->assertTrue($bag->has('foo.bar'), '->has() returns true (foo.bar)');
        $this->assertFalse($bag->has('foo.bar.baz.zeek'), '->has() returns false (for.bar.baz.zeek)');
        $this->assertFalse($bag->has('does_not_exist'), '->has() returns false (does_not_exist)');
        $this->assertFalse($bag->has('foo.does_not_exist'), '->has() returns false (foo.does_not_exist)');
        $this->assertFalse($bag->has('does_not_exist.foo.bar'), '->has() returns false (does_not_exist.foo.bar)');

        $this->assertTrue(isset($bag['foo']), 'isset() returns true');
        $this->assertNull($bag['does_not_exist'], '$bag[...] returns null');
        $this->assertNull($bag['foo.does.not.exist'], '$bag[...] returns null (for nested variable)');
        $this->assertEquals($nested, $bag['foo'], '$bag[...] returns nested array');
        $this->assertEquals($nested['bar'], $bag['foo.bar'], '$bag[...] returns nested variable');
        $this->assertEquals($nested['bar'], $bag['foo']['bar'], '$bag[...][...] returns nested variable');
    }

    /**
     * @covers PHPDOC\Component\PropertyBag::count
     */
    public function testCountable()
    {
        $bag = new PropertyBag(array('foo' => 'bar', 'baz' => 'zeek'));
        $this->assertEquals(2, count($bag), 'count() returns proper value');
    }

}
