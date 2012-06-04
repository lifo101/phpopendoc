<?php

use PHPDOC\Component\PropertyBag;

class PropertyBagTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @--covers PHPDOC\Component\PropertyBag::__construct
	 * @--covers PHPDOC\Component\PropertyBag::all
	 */
	public function testAll()
	{
		$ary = array('foo' => 'bar');
		$bag = new PropertyBag($ary);

		$this->assertEquals($ary, $bag->all(), '->all() returns all properties');
	}

	/**
	 * @--covers PHPDOC\Component\PropertyBag::set
	 * @--covers PHPDOC\Component\PropertyBag::offsetSet
	 */
	public function testSet()
	{
		$bag = new PropertyBag();

		$bag->set('foo', 'bar');
		$this->assertEquals('bar', $bag->get('foo'), '->set() assigned value properly');

		$bag['baz'] = 'zeek';
		$this->assertEquals('zeek', $bag->get('baz'), '$bag[...] = "value" assigned value properly');
	}

	/**
	 * @--covers PHPDOC\Component\PropertyBag::set
	 * @expectedException InvalidArgumentException
	 */
	public function testSetException()
	{
		$bag = new PropertyBag();
		$bag->set('foo.bar', 'baz');	// throws InvalidArgumentException
	}
	
	/**
	 * @--covers PHPDOC\Component\PropertyBag::remove
	 * @expectedException InvalidArgumentException
	 */
	public function testRemoveException()
	{
		$bag = new PropertyBag();
		$bag->remove('foo.bar'); 		// throws InvalidArgumentException
	}

	/**
	 * @--covers PHPDOC\Component\PropertyBag::getIterator
	 */
	public function testIterator()
	{
		$ary = array('foo' => 'bar', 'baz' => 'zeek');
		$bag = new PropertyBag($ary);
		$ary2 = array();
		foreach ($bag as $key => $val) {	// cause getIterator to be called
			$ary2[$key] = $val;
		}
		$this->assertEquals($ary2, $ary, '$bag getIterator works');
	}
	
	/**
	 * @--covers PHPDOC\Component\PropertyBag::get
	 * @--covers PHPDOC\Component\PropertyBag::has
	 */
	public function testGet()
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
		$this->assertTrue($bag->has('foo.bar.baz'), '->has() returns true (for.bar.baz)');
		$this->assertFalse($bag->has('foo.bar.baz.zeek'), '->has() returns false (for.bar.baz.zeek)');
		$this->assertFalse($bag->has('does_not_exist'), '->has() returns false (does_not_exist)');
		$this->assertFalse($bag->has('foo.does_not_exist'), '->has() returns false (foo.does_not_exist)');
		$this->assertFalse($bag->has('does_not_exist.foo.bar'), '->has() returns false (does_not_exist.foo.bar)');
	}

	/**
	 * @--covers PHPDOC\Component\PropertyBag::remove
	 * @--covers PHPDOC\Component\PropertyBag::offsetUnset
	 */
	public function testRemove()
	{
		$bag = new PropertyBag(array('foo' => 'bar', 'baz' => 'zeek'));
		$bag->remove('foo');
		$this->assertEquals(array('baz' => 'zeek'), $bag->all(), '->remove() removed property');

		$bag = new PropertyBag(array('foo' => 'bar', 'baz' => 'zeek'));
		unset($bag['foo']);
		$this->assertEquals(array('baz' => 'zeek'), $bag->all(), 'unset($bag[...]) removed property');

		//$bag = new PropertyBag(array('foo' => array('bar' => 'zeek'), 'baz' => 'zeek'));
		//$bag->remove('foo.bar');
		//$this->assertEquals(array('baz' => 'zeek'), $bag->all(), '->remove() removed property (for nested variable)');
	}

	/**
	 * @--covers PHPDOC\Component\PropertyBag::count
	 */
	public function testCountable()
	{
		$bag = new PropertyBag(array('foo' => 'bar', 'baz' => 'zeek'));
		$this->assertEquals(2, count($bag), 'count() returns value');
	}

	/**
	 * @--covers PHPDOC\Component\PropertyBag::offsetGet
	 * @--covers PHPDOC\Component\PropertyBag::offsetExists
	 */
	public function testArrayAccess()
	{
		$nested = array('bar' => 'baz');
		$ary = array('foo' => $nested);
		$bag = new PropertyBag($ary);

		$this->assertTrue(isset($bag['foo']), 'isset() returns true');
		$this->assertNull($bag['does_not_exist'], '$bag[...] returns null');
		$this->assertNull($bag['foo.does.not.exist'], '$bag[...] returns null (for nested variable)');
		$this->assertEquals($nested, $bag['foo'], '$bag[...] returns nested array');
		$this->assertEquals($nested['bar'], $bag['foo.bar'], '$bag[...] returns nested variable');
		$this->assertEquals($nested['bar'], $bag['foo']['bar'], '$bag[...][...] returns nested variable');
	}
}
