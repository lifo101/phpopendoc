<?php

use PHPDOC\Document,
	PHPDOC\Element\Section
	;

class DocumentTest extends \PHPUnit_Framework_TestCase
{
	public function testConstructor()
	{
		$doc = new Document();
		$this->assertTrue($doc instanceof Document, 'new Document() returns object');
	}

	public function testAddSection()
	{
		$doc = new Document();
		$sec = $doc->addSection();
		$this->assertTrue($sec instanceof Section, '->addSection() created a new section');

		$sec = new Section();
		$expected = $doc->addSection($sec);
		$this->assertSame($expected, $sec, '->addSection($object) added a pre-existing section');
		
		$expected = new Section();
		$doc['new'] = $expected;
		$this->assertSame($expected, $doc->getSection('new'), '$doc[...] added a pre-existing section');
	}
	
	/**
	 * @expectedException UnexpectedValueException
	 */
	public function testAddSectionException()
	{
		$doc = new Document();
		$doc['new'] = new \DateTime();	// throws UnexpectedValueException
	}
	
	public function testRemoveSection()
	{
		$doc = new Document();
		$sec = $doc->addSection('foobar');
		
		$this->assertTrue($doc->hasSection('foobar'), '->hasSection() returns true');
		$doc->removeSection('foobar');
		$this->assertFalse($doc->hasSection('foobar'), '->hasSection() returns false after section is removed');

		$sec = $doc->addSection('baz');
		unset($doc['baz']);
		$this->assertFalse($doc->hasSection('baz'), 'unset($doc[...]) removed a named section');
	}
	
	public function testGetSection()
	{
		$doc = new Document();
		$expected = $doc->addSection('test');

		$this->assertTrue(isset($doc['test']), 'isset($doc[...]) returned true');
		$this->assertSame($expected, $doc->getSection(), '->getSection() returns current section');
		$this->assertSame($expected, $doc->getSection('test'), '->getSection(\'name\') returns section');
		$this->assertSame($expected, $doc['test'], '$doc[\'name\'] returns section');
	}

	/**
	 * @expectedException OutOfBoundsException
	 */
	public function testGetSectionException()
	{
		$doc = new Document();
		$doc->addSection();
		$doc['does_not_exist'];	// throws OutOfBoundsException
	}

	/**
	 * @expectedException OutOfBoundsException
	 */
	public function testGetCurrentSectionException()
	{
		$doc = new Document();
		$doc->getSection();		// throws OutOfBoundsException (since no sections have been added)
	}

	/**
	 * @expectedException OutOfBoundsException
	 */
	public function testGetUnknownSectionException()
	{
		$doc = new Document();
		$doc->addSection();
		$doc->getSection('does_not_exist');	// throws OutOfBoundsException
	}
}
