<?php

use PHPDOC\Document,
	PHPDOC\Element\Section,
	PHPDOC\Element\Text
	;

class TextTest extends \PHPUnit_Framework_TestCase
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
	
	public function testText()
	{
		$text = new Text('The quick brown fox');
	}
	
}
