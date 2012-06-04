<?php

use PHPDOC\Document,
	PHPDOC\Element\Section
	;

class SectionTest extends \PHPUnit_Framework_TestCase
{
	protected $doc;
	protected $sec;
	
	public function setUp()
	{
		$this->doc = new Document();
		$this->sec = $this->doc->addSection('test');
	}
	
	public function tearDown()
	{
		unset($this->doc, $this->sec);
	}
	
	public function testConstructor()
	{
		
	}
	
}
